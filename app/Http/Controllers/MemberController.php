<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Setting; // <-- Asegúrate de importar Setting
use App\Models\Subscription;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache; // <-- Asegúrate de importar Cache
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMemberMail;
use Illuminate\Mail\Mailer;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Arr;


class MemberController extends Controller
{
    // ... (métodos index, create - sin cambios) ...
    public function index(Request $request)
    {
        $query = Member::query()->with('latestSubscription');
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('member_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->active();
            } elseif ($request->status == 'inactive') {
                $query->inactive();
            }
        }
        $members = $query->orderBy('name')->paginate(15);
        return view('members.index', compact('members'));
    }

    public function create()
    {
        $membershipTypes = MembershipType::all();
        return view('members.create', compact('membershipTypes'));
    }


    /**
     * Store a newly created resource in storage.
     * (Esta es la lógica que implementa el prefijo configurable)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:members',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_student' => 'nullable|boolean',
            'membership_type_id' => 'nullable|exists:membership_types,id'
        ]);

        $isStudent = $request->has('is_student');
        $photoRelativePath = null;

        if ($request->hasFile('profile_photo')) {
            $photoRelativePath = $request->file('profile_photo')->store('member_photos', 'public');
        }

        // --- INICIO LÓGICA DE GUARDADO EN 2 PASOS ---
        // 1. Crear el miembro SIN el member_code
        $member = new Member();
        $member->name = $validated['name'];
        $member->phone = $validated['phone'];
        $member->email = $validated['email'];
        $member->profile_photo_path = $photoRelativePath;
        $member->is_student = $isStudent;
        $member->status = 'expired';
        $member->save(); // <-- 1er Guardado (aquí es donde fallaba)

        // 2. Generar el Código de Miembro AHORA que tenemos $member->id
        // Obtener el prefijo de la Configuración (cacheado para velocidad)
        $settingsCache = Cache::remember('global_settings', 60*60, function () {
             return Setting::pluck('value', 'key');
        });
        $prefix = $settingsCache->get('member_code_prefix', 'GYM-'); // Valor por defecto 'GYM-'
        
        // Asignar el código (ej: "GYM-1" o "1" si el prefijo está vacío)
        $member->member_code = $prefix . $member->id;
        // --- FIN LÓGICA DE GUARDADO ---


        // Lógica de Suscripción (si aplica)
        if ($request->filled('membership_type_id')) {
            $type = MembershipType::find($validated['membership_type_id']);
            $amount = $isStudent ? $type->price_student : $type->price_general;
            
            $payment = Payment::create([
                'member_id' => $member->id,
                'user_id' => Auth::id(),
                'amount' => $amount,
                'payment_date' => Carbon::today()
            ]);
            
            $startDate = Carbon::today();
            $endDate = $startDate->copy()->addDays($type->duration_days);
            Subscription::create([
                'member_id' => $member->id,
                'membership_type_id' => $type->id,
                'payment_id' => $payment->id,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            $member->status = 'active';
        }
        
        // 3. Guardar el miembro por segunda vez (AHORA SÍ con el código y el estado)
        $member->save();

        // --- Enviar Correo de Bienvenida ---
        if ($member->email) {
            try {
                $mailSettings = Cache::remember('mail_settings', 60*60, function () {
                    return Setting::where('key', 'like', 'mail_%')->pluck('value', 'key');
                });

                if (!empty($mailSettings['mail_username']) && !empty($mailSettings['mail_password'])) {
                     $mailConfig = [
                        'transport' => $mailSettings['mail_mailer'],
                        'host' => $mailSettings['mail_host'],
                        'port' => $mailSettings['mail_port'],
                        'encryption' => $mailSettings['mail_encryption'],
                        'username' => $mailSettings['mail_username'],
                        'password' => $mailSettings['mail_password'],
                        'timeout' => null,
                        'local_domain' => env('MAIL_EHLO_DOMAIN'),
                    ];
                    $fromAddress = $mailSettings['mail_from_address'];
                    $fromName = $mailSettings['mail_from_name'] ?? $settingsCache->get('gym_name', config('app.name'));

                    $mailer = app()->makeWith('mailer', ['name' => 'welcome_smtp']);
                    $transport = app('mail.manager')->createSymfonyTransport($mailConfig);
                    $dynamicMailer = new Mailer('welcome_smtp', app('view'), $transport, app('events'));
                    $dynamicMailer->alwaysFrom($fromAddress, $fromName);
                    $dynamicMailer->to($member->email)
                                  ->send(new WelcomeMemberMail($member, $fromName, $fromAddress, $fromName));
                } else {
                     Log::warning('Configuración de correo incompleta. No se envió bienvenida a: ' . $member->email);
                }
            } catch (Exception $e) { report($e); }
        }

        return redirect()->route('members.index')->with('success', 'Miembro registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        return redirect()->route('members.edit', $member);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        $membershipTypes = MembershipType::all();
        return view('members.edit', compact('member', 'membershipTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Member $member)
    {
         $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:members,email,' . $member->id,
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_student' => 'nullable|boolean',
        ]);

        $member->name = $validated['name'];
        $member->phone = $validated['phone'];
        $member->email = $validated['email'];
        $member->is_student = $request->has('is_student');

        if ($request->hasFile('profile_photo')) {
            if ($member->profile_photo_path) {
                 Storage::disk('public')->delete($member->profile_photo_path);
            }
            $member->profile_photo_path = $request->file('profile_photo')->store('member_photos', 'public');
        }

        $member->save();

        return redirect()->route('members.index')->with('success', 'Miembro actualizado exitosamente.');
    }

     /**
     * Remove the specified resource from storage.
     */
     public function destroy(Member $member)
    {
        try {
            $memberName = $member->name;
            $member->delete();
            return redirect()->route('members.index')->with('success', "Miembro '{$memberName}' eliminado exitosamente.");
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('members.index')->with('error', 'Error al eliminar al miembro.');
        }
    }

    /**
     * Muestra el formulario de renovación.
     */
    public function showRenewForm(Member $member)
    {
        $membershipTypes = MembershipType::all();
        return view('members.renew', compact('member', 'membershipTypes'));
    }

    /**
     * Procesa la renovación.
     */
    public function processRenewal(Request $request, Member $member)
    {
        $validated = $request->validate([
            'membership_type_id' => 'required|exists:membership_types,id'
        ]);

        try {
            $type = MembershipType::find($validated['membership_type_id']);
            $amount = $member->is_student ? $type->price_student : $type->price_general;

            $payment = Payment::create([
                'member_id' => $member->id,
                'user_id' => Auth::id(), // Guarda quién registró la renovación
                'amount' => $amount,
                'payment_date' => Carbon::today()
            ]);

            $startDate = Carbon::today();
            $endDate = $startDate->copy()->addDays($type->duration_days);

            Subscription::create([
                'member_id' => $member->id,
                'membership_type_id' => $type->id,
                'payment_id' => $payment->id,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            $member->status = 'active';
            $member->save();

        } catch (Exception $e) {
            report($e);
            return redirect()->route('members.edit', $member)->with('error', 'Ocurrió un error al procesar la renovación.');
        }

        return redirect()->route('members.index')->with('success', "Renovación para {$member->name} registrada exitosamente.");
    }
}