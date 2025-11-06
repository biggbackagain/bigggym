<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CashMovementController extends Controller
{
    /**
     * Muestra la lista de movimientos y el formulario para añadir uno nuevo.
     */
    public function index(Request $request)
    {
        $request->validate(['date' => 'nullable|date']);
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        $movements = CashMovement::whereDate('created_at', $date)
                                 ->with('user') // Cargar el usuario que lo registró
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        // Calcular saldo del día (simple: entradas - salidas)
        $dailyBalance = $movements->where('type', 'entry')->sum('amount') - $movements->where('type', 'exit')->sum('amount');

        return view('cash-movements.index', compact('movements', 'date', 'dailyBalance'));
    }

    /**
     * Guarda un nuevo movimiento de caja.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:entry,exit',
            'amount' => 'required|numeric|min:0.01', // No permitir 0
            'description' => 'required|string|max:255',
        ]);

        CashMovement::create([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'user_id' => Auth::id(), // Guarda el ID del usuario logueado
        ]);

        // Redirige de vuelta a la fecha actual para ver el movimiento recién creado
        return redirect()->route('cash.index', ['date' => Carbon::today()->format('Y-m-d')])
                         ->with('success', 'Movimiento registrado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     * (Opcional: Si quieres añadir un botón de eliminar en la tabla)
     */
     public function destroy(CashMovement $cashMovement)
    {
        $cashMovement->delete();
      return back()->with('success', 'Movimiento eliminado.');
    }
}