<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\CashMovement;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Mail\SalesReportMail;
use Illuminate\Mail\Mailer;
use Exception;
use Illuminate\Support\Facades\Log;

class SalesReportController extends Controller
{
    /**
     * Muestra el reporte de caja (Final).
     */
    public function index(Request $request)
    {
        // 1. Validar las fechas de entrada
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date', 
        ],[
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.'
        ]);

        // 2. Determinar el rango de fechas
        $startDate = $request->filled('start_date') ? Carbon::parse($validated['start_date'])->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($validated['end_date'])->endOfDay() : $startDate->copy()->endOfDay();

        // --- VENTAS DE PRODUCTOS ---
        $productSales = Sale::whereNull('deleted_at')
                           ->whereBetween('created_at', [$startDate, $endDate])
                           ->with('products') 
                           ->orderBy('created_at', 'desc')
                           ->get();
        $totalProductSalesAmount = $productSales->sum('total_amount');
        $totalProductSalesCount = $productSales->count();
        $topProduct = $productSales->flatMap(fn($sale) => $sale->products)
                                  ->groupBy('name')
                                  ->map(fn($group) => $group->sum('pivot.quantity'))
                                  ->sortDesc()
                                  ->keys()
                                  ->first();

        // --- PAGOS DE MEMBRESÍAS (ACTUALIZADO CON MÉTODO Y REFERENCIA) ---
        $membershipPayments = Payment::whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])
                                     ->with(['member', 'subscription.membershipType'])
                                     ->orderBy('created_at', 'desc')
                                     ->get();
        
        $totalMembershipPaymentsAmount = $membershipPayments->sum('amount');
        $totalMembershipPaymentsCount = $membershipPayments->count();

        // Cálculo de totales por método (Membresías)
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
        $cashMovements = CashMovement::whereBetween('created_at', [$startDate, $endDate])
                                      ->with('user')
                                      ->orderBy('created_at', 'desc')
                                      ->get();
        $totalCashEntries = $cashMovements->where('type', 'entry')->sum('amount');
        $totalCashExits = $cashMovements->where('type', 'exit')->sum('amount');
        $netCashMovement = $totalCashEntries - $totalCashExits;

        // --- CALCULAR TOTAL GENERAL DE CAJA ---
        $grandTotal = $totalProductSalesAmount + $totalMembershipPaymentsAmount + $netCashMovement;

        // Recuperamos settings globales para la vista
        $globalSettings = Cache::get('global_settings', fn() => Setting::pluck('value', 'key'));

        return view('sales-report.index', compact(
            'startDate',
            'endDate',
            'productSales',
            'totalProductSalesAmount',
            'totalProductSalesCount',
            'topProduct',
            'membershipPayments',
            'totalMembershipPaymentsAmount',
            'totalMembershipPaymentsCount',
            'methodTotals',
            'cashMovements',
            'totalCashEntries',
            'totalCashExits',
            'netCashMovement',
            'grandTotal',
            'globalSettings'
        ));
    }
    
    /**
     * Envía el reporte de caja por correo electrónico.
     */
    public function sendEmailReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $startDate = $request->filled('start_date') ? Carbon::parse($validated['start_date'])->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($validated['end_date'])->endOfDay() : $startDate->copy()->endOfDay();

        $mailSettings = Cache::remember('mail_settings', 60*60, function () {
            return Setting::where('key', 'like', 'mail_%')->pluck('value', 'key');
        });

        $globalSettings = Cache::get('global_settings', fn() => Setting::pluck('value', 'key'));
        $recipientEmail = $globalSettings->get('report_recipient_email');

        if (empty($mailSettings->get('mail_username')) || empty($recipientEmail)) {
            return back()->with('error', 'Configuración de correo o destinatario incompleta.');
        }

        // Obtener datos para el reporte de correo
        $productSales = Sale::whereNull('deleted_at')
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->get();

        $membershipPayments = Payment::whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])
                                     ->with(['subscription.membershipType', 'member'])
                                     ->get();

        $cashMovements = CashMovement::whereBetween('created_at', [$startDate, $endDate])
                                     ->get();

        $reportData = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'productSales' => $productSales,
            'totalProductSalesAmount' => $productSales->sum('total_amount'),
            'totalProductSalesCount' => $productSales->count(),
            'membershipPayments' => $membershipPayments,
            'totalMembershipPaymentsAmount' => $membershipPayments->sum('amount'),
            'totalMembershipPaymentsCount' => $membershipPayments->count(),
            'totalCashEntries' => $cashMovements->where('type', 'entry')->sum('amount'),
            'totalCashExits' => $cashMovements->where('type', 'exit')->sum('amount'),
            'netCashMovement' => $cashMovements->where('type', 'entry')->sum('amount') - $cashMovements->where('type', 'exit')->sum('amount'),
            'grandTotal' => $productSales->sum('total_amount') + $membershipPayments->sum('amount') + ($cashMovements->where('type', 'entry')->sum('amount') - $cashMovements->where('type', 'exit')->sum('amount')),
        ];

        try {
            $transport = app('mail.manager')->createSymfonyTransport([
                'transport' => $mailSettings->get('mail_mailer'),
                'host' => $mailSettings->get('mail_host'),
                'port' => $mailSettings->get('mail_port'),
                'encryption' => $mailSettings->get('mail_encryption'),
                'username' => $mailSettings->get('mail_username'),
                'password' => $mailSettings->get('mail_password'),
            ]);

            $dynamicMailer = new Mailer('report_smtp', app('view'), $transport, app('events'));
            $gymName = $globalSettings->get('gym_name', config('app.name'));
            $fromAddress = $mailSettings->get('mail_from_address');

            $dynamicMailer->alwaysFrom($fromAddress, $gymName);

            $dynamicMailer->to($recipientEmail)->send(new SalesReportMail($reportData, $fromAddress, $gymName));

            return back()->with('success', 'Reporte de caja enviado exitosamente a ' . $recipientEmail);
        } catch (Exception $e) {
            Log::error('Fallo al enviar reporte: ' . $e->getMessage());
            return back()->with('error', 'Error al enviar el correo: ' . $e->getMessage());
        }
    }
}