<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Carbon\Carbon;

class CheckInController extends Controller
{
    /**
     * Muestra la vista de check-in.
     */
    public function index()
    {
        return view('check-in.index');
    }

    /**
     * Procesa el intento de check-in.
     */
    public function store(Request $request)
    {
        $request->validate(['member_code' => 'required|string']);

        $member = Member::where('member_code', $request->member_code)->first();

        // Caso 1: No se encuentra el miembro
        if (!$member) {
            return redirect()->route('check-in.index')
                ->with('status', 'error')
                ->with('message', 'Código de miembro no encontrado.');
        }

        // Caso 2: Se encuentra el miembro, buscamos su última suscripción
        $subscription = $member->subscriptions()->latest('end_date')->first();

        // Caso 3: Tiene suscripción y está activa
        if ($subscription && $subscription->end_date >= Carbon::today()) {
            
            $member->status = 'active';
            $member->save();
            
            return redirect()->route('check-in.index')
                ->with('status', 'success')
                ->with('message', "Acceso Permitido: {$member->name}")
                ->with('member_name', $member->name)
                ->with('end_date', $subscription->end_date->format('d/m/Y'))
                ->with('photo_path', $member->profile_photo_path); // <-- LÍNEA AÑADIDA
        }

        // Caso 4: No tiene suscripción o está vencida
        $member->status = 'expired';
        $member->save();

        return redirect()->route('check-in.index')
            ->with('status', 'error')
            ->with('message', "Acceso Denegado: {$member->name}. Membresía Vencida.")
            ->with('member_name', $member->name)
            ->with('photo_path', $member->profile_photo_path); // <-- LÍNEA AÑADIDA
    }
}