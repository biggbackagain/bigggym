<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\CashMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; // Importar Auth

class MyReportController extends Controller
{
    /**
     * Muestra el reporte de caja (MI CORTE) para el usuario logueado y un rango de fechas.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ],[
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.'
        ]);

        $startDate = $request->filled('start_date') ? Carbon::parse($validated['start_date'])->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($validated['end_date'])->endOfDay() : $startDate->copy()->endOfDay();

        $userId = Auth::id(); // <-- OBTENER ID DEL CAJERO

        // --- VENTAS DE PRODUCTOS (FILTRADO POR USUARIO) ---
        $productSales = Sale::where('user_id', $userId) // <-- FILTRO
                           ->whereBetween('created_at', [$startDate, $endDate])
                           ->with('products')
                           ->orderBy('created_at', 'desc')
                           ->get();
        $totalProductSalesAmount = $productSales->sum('total_amount');

        // --- PAGOS DE MEMBRESÍAS (FILTRADO POR USUARIO) ---
        $membershipPayments = Payment::where('user_id', $userId) // <-- FILTRO
                                     ->whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])
                                     ->with(['member', 'subscription.membershipType'])
                                     ->orderBy('created_at', 'desc')
                                     ->get();
        $totalMembershipPaymentsAmount = $membershipPayments->sum('amount');

        // --- MOVIMIENTOS DE CAJA (FILTRADO POR USUARIO) ---
        $cashMovements = CashMovement::where('user_id', $userId) // <-- FILTRO
                                      ->whereBetween('created_at', [$startDate, $endDate])
                                      ->with('user')
                                      ->orderBy('created_at', 'desc')
                                      ->get();
        $totalCashEntries = $cashMovements->where('type', 'entry')->sum('amount');
        $totalCashExits = $cashMovements->where('type', 'exit')->sum('amount');
        $netCashMovement = $totalCashEntries - $totalCashExits;

        // --- CALCULAR TOTAL DEL CAJERO ---
        $grandTotal = $totalProductSalesAmount + $totalMembershipPaymentsAmount + $netCashMovement;

        // Reutilizamos la vista del reporte general, pero con datos filtrados
        return view('my-report.index', compact(
            'startDate', 'endDate', 'productSales', 'totalProductSalesAmount',
            'membershipPayments', 'totalMembershipPaymentsAmount',
            'cashMovements', 'totalCashEntries', 'totalCashExits', 'netCashMovement',
            'grandTotal'
        ));
    }
}