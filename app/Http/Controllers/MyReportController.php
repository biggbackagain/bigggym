<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\CashMovement;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MyReportController extends Controller
{
    /**
     * Muestra el reporte de caja (MI CORTE) para el usuario logueado.
     */
    public function index(Request $request)
    {
        // 1. Validar Fechas
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // 2. Determinar el rango de fechas
        $startDate = $request->filled('start_date') ? Carbon::parse($validated['start_date'])->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($validated['end_date'])->endOfDay() : $startDate->copy()->endOfDay();

        $userId = Auth::id();

        // --- VENTAS DE PRODUCTOS ---
        $productSales = Sale::where('user_id', $userId)
                           ->whereBetween('created_at', [$startDate, $endDate])
                           ->with('products')
                           ->orderBy('created_at', 'desc')
                           ->get();
        $totalProductSalesAmount = $productSales->sum('total_amount');

        // --- PAGOS DE MEMBRESÍAS (CON MÉTODO Y REFERENCIA) ---
        $membershipPayments = Payment::where('user_id', $userId)
                                     ->whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])
                                     ->with(['member', 'subscription.membershipType'])
                                     ->orderBy('created_at', 'desc')
                                     ->get();
        
        $totalMembershipPaymentsAmount = $membershipPayments->sum('amount');

        // Cálculo de totales por método para el cajero
        $methodTotals = [
            'Efectivo' => 0,
            'Tarjeta' => 0,
            'Transferencia' => 0
        ];

        foreach ($membershipPayments as $payment) {
            $method = $payment->subscription->payment_method ?? 'Efectivo';
            if (isset($methodTotals[$method])) {
                $methodTotals[$method] += $payment->amount;
            }
        }

        // --- MOVIMIENTOS DE CAJA ---
        $cashMovements = CashMovement::where('user_id', $userId)
                                      ->whereBetween('created_at', [$startDate, $endDate])
                                      ->with('user')
                                      ->orderBy('created_at', 'desc')
                                      ->get();
        $totalCashEntries = $cashMovements->where('type', 'entry')->sum('amount');
        $totalCashExits = $cashMovements->where('type', 'exit')->sum('amount');
        $netCashMovement = $totalCashEntries - $totalCashExits;

        // --- TOTAL FINAL ---
        $grandTotal = $totalProductSalesAmount + $totalMembershipPaymentsAmount + $netCashMovement;

        // Configuración global (ej. nombre del gym)
        $globalSettings = Cache::get('global_settings', fn() => Setting::pluck('value', 'key'));

        return view('my-report.index', compact(
            'startDate', 'endDate',
            'productSales', 'totalProductSalesAmount',
            'membershipPayments', 'totalMembershipPaymentsAmount',
            'methodTotals', // <-- ENVIAMOS LOS TOTALES POR MÉTODO
            'cashMovements', 'totalCashEntries', 'totalCashExits', 'netCashMovement',
            'grandTotal',
            'globalSettings'
        ));
    }
}