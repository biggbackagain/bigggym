<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\BiometricClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BiometricController extends Controller
{
    private function biometricsEnabled(): bool
    {
        $settings = Cache::remember('global_settings', 60 * 60, fn() => Setting::pluck('value', 'key'));
        return ($settings->get('biometrics_enabled', '0') === '1') && config('services.biometrics.enabled');
    }

    public function extractVector(Request $request, BiometricClient $biometrics): JsonResponse
    {
        if (!$this->biometricsEnabled()) {
            return response()->json(['success' => false, 'message' => 'El reconocimiento facial está desactivado.'], 503);
        }
        $request->validate(['image' => 'required|string|max:6000000']);
        try {
            $response = $biometrics->connection()->post('/api/extract-vector', [
                'image_base64' => $request->image,
            ]);
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            // Do not expose credentials or biometric payloads in the response.
        }
        return response()->json([
            'success' => false,
            'message' => 'El servicio de reconocimiento no está disponible. Reintenta más tarde.',
        ], 503);
    }
}
