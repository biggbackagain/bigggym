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
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
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
            if ($request->status == 'active') { $query->active(); } 
            elseif ($request->status == 'inactive') { $query->inactive(); }
        }
        $members = $query->orderBy('name')->paginate(15);
        return view('members.index', compact('members'));
    }

    public function create()
    {
        $membershipTypes = MembershipType::all();
        return view('members.create', compact('membershipTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:members',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_student' => 'nullable|boolean',
            'membership_type_id' => 'nullable|exists:membership_types,id',
            'payment_method' => 'required_with:membership_type_id|string|nullable',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        $photoRelativePath = $request->hasFile('profile_photo') 
            ? $request->file('profile_photo')->store('member_photos', 'public') 
            : null;

        // 1. Se crea el miembro base
        $member = new Member();
        $member->name = $validated['name'];
        $member->phone = $validated['phone'];
        $member->email = $validated['email'];
        $member->profile_photo_path = $photoRelativePath;
        $member->is_student = $request->has('is_student');
        $member->status = 'expired';
        $member->save();

        // 2. Se le asigna su código de acceso
        $settings = Cache::remember('global_settings', 60*60, fn() => Setting::pluck('value', 'key'));
        $member->member_code = ($settings->get('member_code_prefix', 'GYM-')) . $member->id;
        $member->save();

        // 3. Si eligió membresía, se le cobra y se activa
        if ($request->filled('membership_type_id')) {
            $type = MembershipType::find($validated['membership_type_id']);
            $amount = $member->is_student ? $type->price_student : $type->price_general;
            
            $payment = Payment::create([
                'member_id' => $member->id,
                'user_id' => Auth::id(),
                'amount' => $amount,
                'payment_date' => Carbon::today()
            ]);
            
            $subscription = Subscription::create([
                'member_id' => $member->id,
                'membership_type_id' => $type->id,
                'payment_id' => $payment->id,
                'start_date' => Carbon::today(),
                'end_date' => Carbon::today()->addDays($type->duration_days),
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference
            ]);
            $member->update(['status' => 'active']);
            
            $this->sendNotification($member, $subscription, $settings);
            // ELIMINAMOS la línea duplicada que estaba aquí
        }

        // 4. Redirigimos pasando el código generado y el nombre a la vista
        return redirect()->route('members.index')
            ->with('success', '¡Miembro registrado con éxito!')
            ->with('print_receipt', $member->member_code)
            ->with('new_member_name', $member->name);
    }

    public function edit(Member $member)
    {
        $membershipTypes = MembershipType::all();
        return view('members.edit', compact('member', 'membershipTypes'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:members,email,' . $member->id,
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($member->profile_photo_path) { Storage::disk('public')->delete($member->profile_photo_path); }
            $member->profile_photo_path = $request->file('profile_photo')->store('member_photos', 'public');
        }

        $member->update(array_merge($validated, ['is_student' => $request->has('is_student')]));
        return redirect()->route('members.index')->with('success', 'Actualizado.');
    }

    public function showRenewForm(Member $member)
    {
        $membershipTypes = MembershipType::all();
        return view('members.renew', compact('member', 'membershipTypes'));
    }

    public function processRenewal(Request $request, Member $member)
    {
        $request->validate([
            'membership_type_id' => 'required|exists:membership_types,id', 
            'payment_method' => 'required',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        $type = MembershipType::find($request->membership_type_id);
        $amount = $member->is_student ? $type->price_student : $type->price_general;

        $payment = Payment::create([
            'member_id' => $member->id,
            'user_id' => Auth::id(),
            'amount' => $amount,
            'payment_date' => Carbon::today()
        ]);

        $subscription = Subscription::create([
            'member_id' => $member->id,
            'membership_type_id' => $type->id,
            'payment_id' => $payment->id,
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays($type->duration_days),
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference
        ]);

        $member->update(['status' => 'active']);
        $this->sendNotification($member, $subscription, Cache::get('global_settings'));

        return redirect()->route('members.index')->with('success', 'Renovado.')->with('print_receipt', $member->member_code);
    }

    public function showReceipt(Member $member)
    {
        $subscription = Subscription::with(['membershipType', 'payment'])->where('member_id', $member->id)->latest()->first();
        $gymName = Cache::get('global_settings')['gym_name'] ?? 'BIGG GYM';
        return view('members.receipt', compact('member', 'subscription', 'gymName'));
    }

    private function sendNotification($member, $subscription, $settings)
    {
        if (!$member->email) return;
        try {
            $mailSettings = Setting::where('key', 'like', 'mail_%')->pluck('value', 'key');
            $gymName = $settings->get('gym_name', config('app.name'));
            $fromAddress = $mailSettings->get('mail_from_address');
            $fromName = $mailSettings->get('mail_from_name') ?? $gymName;

            $transport = app('mail.manager')->createSymfonyTransport([
                'transport' => $mailSettings->get('mail_mailer'),
                'host' => $mailSettings->get('mail_host'),
                'port' => $mailSettings->get('mail_port'),
                'encryption' => $mailSettings->get('mail_encryption'),
                'username' => $mailSettings->get('mail_username'),
                'password' => $mailSettings->get('mail_password'),
            ]);

            $mailer = new Mailer('welcome_smtp', app('view'), $transport, app('events'));
            $mailer->alwaysFrom($fromAddress, $fromName);
            $mailer->to($member->email)->send(new WelcomeMemberMail($member, $subscription, $gymName, $fromAddress, $fromName));
        } catch (Exception $e) { Log::error("Email error: " . $e->getMessage()); }
    }

    public function destroy(Member $member) { $member->delete(); return redirect()->back(); }
}