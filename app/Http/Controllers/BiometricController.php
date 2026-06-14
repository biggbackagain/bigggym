<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class BiometricController extends Controller
{
    /**
     * Recibe la foto en Base64 desde Vue y la envía al contenedor de Python.
     */
    public function extractVector(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|string'
        ]);

        try {
            // Hacemos la petición HTTP interna a la red de Docker.
            // Usamos "biggym-ai" (el nombre del contenedor) y el puerto interno 8000.
            $response = Http::timeout(10)->post('http://biggym-ai:8000/api/extract-vector', [
                'image_base64' => $request->image
            ]);

            if ($response->successful()) {
                // Retornamos la respuesta de Python (que incluye el vector de 128 números) directamente a Vue
                return response()->json($response->json());
            }

            return response()->json([
                'success' => false, 
                'message' => 'Error en el procesamiento del motor biométrico.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'El microservicio de IA no está respondiendo. Verifica que el contenedor esté activo.'
            ], 500);
        }
    }
}