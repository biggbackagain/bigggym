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

    public function biometricCheckIn(Request $request, BiometricClient $biometrics)
    {
        if (!config('services.biometrics.enabled')) {
            return response()->json(['status' => 'disabled', 'message' => 'Reconocimiento desactivado. Usa tu código de socio.'], 503);
        }
        $request->validate(['image' => 'required|string|max:6000000']);
        $members = Member::whereNotNull('face_vector')->get(['id', 'face_vector'])
            ->filter(fn ($member) => is_array($member->face_vector)
                && count($member->face_vector) === 512
                && collect($member->face_vector)->every(fn ($value) => is_numeric($value) && is_finite((float) $value)))
            ->map(fn ($member) => ['id' => $member->id, 'vector' => array_values($member->face_vector)])
            ->values()->all();

        if (empty($members)) {
            return response()->json(['status' => 'waiting', 'message' => 'No hay rostros registrados. Usa tu código de socio.']);
        }

        try {
            $response = $biometrics->connection()->post('/api/recognize', [
                    'image_base64' => $request->image,
                    'known_faces' => $members,
                ]);
            $result = $response->json();
            if (!$response->successful() || !is_array($result) || ($result['success'] ?? false) !== true) {
                return response()->json(['status' => 'unavailable', 'message' => 'Reconocimiento no disponible. Usa tu código o reintenta.'], 503);
            }
            if (($result['match'] ?? false) !== true) {
                return response()->json(['status' => 'waiting', 'message' => $result['message'] ?? 'Mira de frente a la cámara.']);
            }
            if (!isset($result['member_id']) || !in_array($result['member_id'], array_column($members, 'id'), true)) {
                return response()->json(['status' => 'unavailable', 'message' => 'Respuesta de reconocimiento inválida.'], 503);
            }
            return response()->json($this->processAccess(Member::find($result['member_id'])));
        } catch (\Exception $e) {
            return response()->json(['status' => 'unavailable', 'message' => 'Sin conexión al reconocimiento. Usa tu código de socio.'], 503);
        }
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
