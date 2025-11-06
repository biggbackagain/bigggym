<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMemberMail;
use Illuminate\Mail\Mailer;
use Exception;
use Illuminate\Support\Facades\Log; // Import Log facade

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Member::query()->with('latestSubscription');

        // Search Logic
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('member_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status Filter Logic
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $membershipTypes = MembershipType::all();
        return view('members.create', compact('membershipTypes'));
    }


    /**
     * Store a newly created resource in storage.
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
        $photoRelativePath = null; // Variable for relative path

        // Handle Photo Upload (using 'public' disk configured in filesystems.php)
        if ($request->hasFile('profile_photo')) {
            // Store file using 'public' disk, returns relative path e.g., 'member_photos/random.jpg'
            $photoRelativePath = $request->file('profile_photo')->store('member_photos', 'public');
        }

        // Create Member
        $member = Member::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'profile_photo_path' => $photoRelativePath, // Save relative path
            'is_student' => $isStudent,
            'status' => 'expired', // Default status
            'member_code' => 'TEMP' // Temporary code
        ]);

        // Generate Member Code using prefix from settings
        $settingsCache = Cache::remember('global_settings', 60*60, function () {
             return Setting::pluck('value', 'key');
        });
        $prefix = $settingsCache->get('member_code_prefix', 'GYM-'); // Default prefix if not set
        $member->member_code = $prefix . str_pad($member->id, 4, '0', STR_PAD_LEFT);


        // Handle Subscription if selected
        if ($request->filled('membership_type_id')) {
            $type = MembershipType::find($validated['membership_type_id']);
            $amount = $isStudent ? $type->price_student : $type->price_general;
            $payment = Payment::create([
                'member_id' => $member->id,
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
            $member->status = 'active'; // Update status if subscription added
        }

        $member->save(); // Save final member code and status

        // --- START: SEND WELCOME EMAIL ---
        if ($member->email) { // Only if email was provided
            try {
                // Get mail settings (cached or from DB)
                $mailSettings = Cache::remember('mail_settings', 60*60, function () {
                    return Setting::where('key', 'like', 'mail_%')->pluck('value', 'key');
                });

                // Basic validation of mail settings
                if (!empty($mailSettings['mail_username']) && !empty($mailSettings['mail_password']) && !empty($mailSettings['mail_mailer'])) {
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

                    // Create dynamic Mailer instance
                    $mailer = app()->makeWith('mailer', ['name' => 'welcome_smtp']);
                    $transport = app('mail.manager')->createSymfonyTransport($mailConfig);
                    $dynamicMailer = new Mailer('welcome_smtp', app('view'), $transport, app('events'));
                    $dynamicMailer->alwaysFrom($fromAddress, $fromName);

                    // Send the welcome email using the dynamic mailer
                    $dynamicMailer->to($member->email)
                                  ->send(new WelcomeMemberMail($member, $fromName, $fromAddress, $fromName));
                } else {
                    // Log if mail settings are incomplete
                     Log::warning('Configuración de correo incompleta. No se envió bienvenida a: ' . $member->email);
                }

            } catch (Exception $e) {
                // Log any exception during email sending but don't stop the request
                report($e); // Saves the full error to storage/logs/laravel.log
                // Optional: Flash a warning message to the user
                // session()->flash('warning', 'Miembro registrado, pero hubo un error al enviar el correo de bienvenida. Revise los logs.');
            }
        }
        // --- END: SEND WELCOME EMAIL ---

        return redirect()->route('members.index')->with('success', 'Miembro registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        // Redirect to edit page as we don't have a dedicated show page
        return redirect()->route('members.edit', $member);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        $membershipTypes = MembershipType::all(); // Pass membership types
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
            'email' => 'nullable|email|max:255|unique:members,email,' . $member->id, // Ignore self on unique check
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_student' => 'nullable|boolean',
        ]);

        $member->name = $validated['name'];
        $member->phone = $validated['phone'];
        $member->email = $validated['email'];
        $member->is_student = $request->has('is_student');

        // Handle Photo Update
        if ($request->hasFile('profile_photo')) {
            // Delete previous photo if it exists (using relative path)
            if ($member->profile_photo_path) {
                 Storage::disk('public')->delete($member->profile_photo_path);
            }
            // Store new photo using 'public' disk and save relative path
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
             // Photo deletion is handled by the 'deleting' event in the Member model
            $member->delete();
            return redirect()->route('members.index')->with('success', "Miembro '{$memberName}' eliminado exitosamente.");
        } catch (\Exception $e) {
            report($e); // Log the error
            return redirect()->route('members.index')->with('error', 'Error al eliminar al miembro.');
        }
    }

    // ========= START: NEW METHODS FOR RENEWAL =========

    /**
     * Show the form to renew a member's subscription.
     */
    public function showRenewForm(Member $member)
    {
        $membershipTypes = MembershipType::all(); // Get available plans
        return view('members.renew', compact('member', 'membershipTypes'));
    }

    /**
     * Process the renewal form, create payment and subscription.
     */
    public function processRenewal(Request $request, Member $member)
    {
        // 1. Validation (only need the plan ID)
        $validated = $request->validate([
            'membership_type_id' => 'required|exists:membership_types,id'
        ]);

        try {
            $type = MembershipType::find($validated['membership_type_id']);

            // 2. Determine price (based on member's student status)
            $amount = $member->is_student ? $type->price_student : $type->price_general;

            // 3. Create the Payment record
            $payment = Payment::create([
                'member_id' => $member->id,
                'amount' => $amount,
                'payment_date' => Carbon::today() // Today's date
            ]);

            // 4. Calculate dates for the new membership
            $startDate = Carbon::today();
            $endDate = $startDate->copy()->addDays($type->duration_days);

            // 5. Create the new Subscription record
            Subscription::create([
                'member_id' => $member->id,
                'membership_type_id' => $type->id,
                'payment_id' => $payment->id,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            // 6. Update the member's status to ACTIVE
            $member->status = 'active';
            $member->save();

        } catch (Exception $e) {
            report($e); // Log the error
            return redirect()->route('members.edit', $member)->with('error', 'Ocurrió un error al procesar la renovación.');
        }

        // 7. Redirect back to the list (or edit page) with success message
        return redirect()->route('members.index')->with('success', "Renovación para {$member->name} registrada exitosamente.");
    }
    // ========= END: NEW METHODS FOR RENEWAL =========
}