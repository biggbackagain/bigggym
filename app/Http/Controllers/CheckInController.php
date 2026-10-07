<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\BiometricClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class CheckInController extends Controller
{
    public function index()
    {
        $settings = Cache::remember('global_settings', 60 * 60, fn() => Setting::pluck('value', 'key'));
        $biometricsEnabled = ($settings->get('biometrics_enabled', '0') === '1')
            && config('services.biometrics.enabled');
        return view('check-in.index', compact('biometricsEnabled'));
    }

    public function store(Request $request)
    {
        $request->validate(['member_code' => 'required|string|max:100']);
        $member = Member::where('member_code', trim($request->member_code))->first();
        return redirect()->route('check-in.index')->with('access_result', $this->processAccess($member));
    }

    public function biometricCheckIn(Request $request)
    {
        if (!config('services.biometrics.enabled')) {
            return response()->json(['status' => 'disabled', 'message' => 'Reconocimiento desactivado.'], 503);
        }

        $request->validate([
            'member_id' => 'required|exists:members,id',
        ]);

        $member = Member::find($request->member_id);
        return response()->json($this->processAccess($member));
    }

    public function biometricVectors()
    {
        $members = Member::whereNotNull('face_vector')->get(['id', 'name', 'face_vector', 'profile_photo_path'])
            ->map(fn($m) => [
                'id' => $m->id, 
                'name' => $m->name, 
                'photo_path' => $m->profile_photo_path ? \Illuminate\Support\Facades\Storage::url($m->profile_photo_path) : null,
                'vector' => is_string($m->face_vector) ? json_decode($m->face_vector, true) : $m->face_vector
            ])->values()->all();
            
        return response()->json($members);
    }

    private function processAccess(?Member $member): array
    {
        if (!$member) {
            return ['status' => 'error', 'message' => 'Código de socio no encontrado.'];
        }
        $today = Carbon::today();
        $subscription = $member->subscriptions()->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)->latest('end_date')->first();
        $allowed = $subscription !== null;
        $subscription ??= $member->subscriptions()->latest('end_date')->first();
        $member->update(['status' => $allowed ? 'active' : 'expired']);

        return [
            'status' => $allowed ? 'success' : 'error',
            'member_id' => $member->id,
            'name' => $member->name,
            'message' => $allowed ? 'Tu membresía está vigente. ¡Buen entrenamiento!' : 'No tienes una membresía vigente. Acércate a recepción.',
            'gym_name' => Setting::where('key', 'gym_name')->value('value') ?? 'BiggGym',
            'end_date' => $subscription?->end_date->format('d/m/Y'),
            'photo_path' => $member->profile_photo_path ? Storage::url($member->profile_photo_path) : null,
        ];
    }
}


