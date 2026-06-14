<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CheckInController extends Controller
{
    public function index()
    {
        return view('check-in.index');
    }

    // Método manual (cuando teclean el código)
    public function store(Request $request)
    {
        $request->validate(['member_code' => 'required|string']);
        $member = Member::where('member_code', $request->member_code)->first();
        
        $result = $this->processAccess($member);
        return redirect()->route('check-in.index')->with($result);
    }

    // Nuevo método biométrico (cuando la cámara dispara)
    public function biometricCheckIn(Request $request)
    {
        $request->validate(['image' => 'required|string']);

        // 1. Extraemos solo los miembros que ya tienen foto registrada
        $members = Member::whereNotNull('face_vector')->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'vector' => is_string($m->face_vector) ? json_decode($m->face_vector) : $m->face_vector
            ];
        })->toArray();

        if (empty($members)) {
            return response()->json(['status' => 'waiting']);
        }

        try {
            // 2. Mandamos la foto y la base de datos a Python
            $response = Http::timeout(5)->post('http://biggym-ai:8000/api/recognize', [
                'image_base64' => $request->image,
                'known_faces' => $members
            ]);

            $aiResult = $response->json();

            // 3. Si la IA no reconoció a nadie, seguimos esperando
            if (!$aiResult['success'] || !$aiResult['match']) {
                return response()->json(['status' => 'waiting']);
            }

            // 4. ¡Hizo Match! Registramos su asistencia
            $member = Member::find($aiResult['member_id']);
            $result = $this->processAccess($member);
            
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error de conexión IA.']);
        }
    }

    // ... dentro de tu CheckInController.php ...

private function processAccess($member)
{
    if (!$member) {
        return ['status' => 'error', 'message' => 'Código de miembro no encontrado.'];
    }

    $subscription = $member->subscriptions()->latest('end_date')->first();
    $photo = $member->profile_photo_path ? Storage::url($member->profile_photo_path) : null;
    $endDateFormatted = $subscription ? $subscription->end_date->format('d/m/Y') : null;

    // 🟢 CONSULTA DINÁMICA: Lee el valor exacto de la DB que administras en Settings
    $gymName = \App\Models\Setting::where('key', 'gym_name')->value('value') ?? 'BiggGym';

    if ($subscription && $subscription->end_date >= Carbon::today()) {
        $member->update(['status' => 'active']);
        return [
            'status' => 'success',
            'message' => "Acceso Permitido: {$member->name}",
            'gym_name' => $gymName, // Enviamos el nombre dinámico
            'end_date' => $endDateFormatted,
            'photo_path' => $photo
        ];
    }

    $member->update(['status' => 'expired']);
    return [
        'status' => 'error',
        'message' => "Acceso Denegado: {$member->name}. Membresía Vencida.",
        'gym_name' => $gymName, // Enviamos el nombre dinámico
        'end_date' => $endDateFormatted,
        'photo_path' => $photo
    ];
}
    
}