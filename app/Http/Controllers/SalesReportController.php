<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\CashMovement;
use App\Models\Setting; // Asegúrate que esté importado
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail; // Asegúrate que esté importado
use Illuminate\Support\Facades\Cache; // Asegúrate que esté importado
use App\Mail\SalesReportMail; // Asegúrate que esté importado
use Illuminate\Mail\Mailer; // Asegúrate que esté importado
use Exception; // Asegúrate que esté importado
use Illuminate\Support\Facades\Log; // Asegúrate que esté importado

class SalesReportController extends Controller
{
    /**
     * Muestra el reporte de caja (ventas, membresías y movimientos) para un rango de fechas.
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

        // 2. Determinar el rango de fechas a consultar
        $startDate = $request->filled('start_date') ? Carbon::parse($validated['start_date'])->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($validated['end_date'])->endOfDay() : $startDate->copy()->endOfDay();

        // --- VENTAS DE PRODUCTOS ---
        $productSales = Sale::whereBetween('created_at', [$startDate, $endDate])
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

        // --- PAGOS DE MEMBRESÍAS ---
        $membershipPayments = Payment::whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])
                                     ->with(['member', 'subscription.membershipType'])
                                     ->orderBy('created_at', 'desc')
                                     ->get();
        $totalMembershipPaymentsAmount = $membershipPayments->sum('amount');
        $totalMembershipPaymentsCount = $membershipPayments->count();

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

        // Pasar todos los datos calculados a la vista
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
            'cashMovements',
            'totalCashEntries',
            'totalCashExits',
            'netCashMovement',
            'grandTotal'
        ));
    }

    /**
     * Envía el reporte de caja por correo electrónico.
     */
    public function sendEmailReport(Request $request)
    {
         Log::info('--- Iniciando envío de reporte de caja ---'); // <-- Log 1

         // 1. Validar fechas
        $validated = $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ],[
            'end_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la inicial.',
            'start_date.date_format' => 'Formato de fecha inicial inválido (YYYY-MM-DD).',
            'end_date.date_format' => 'Formato de fecha final inválido (YYYY-MM-DD).',
        ]);

        $startDate = $request->filled('start_date') ? Carbon::parse($validated['start_date'])->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($validated['end_date'])->endOfDay() : $startDate->copy()->endOfDay();
        Log::info('Fechas validadas: ' . $startDate->toDateString() . ' a ' . $endDate->toDateString()); // <-- Log 2

        // 2. Obtener configuración y destinatario
        $mailSettings = Cache::remember('mail_settings', 60*60, function () {
            Log::info('Recaching mail_settings for sending report.'); // Log para depuración de caché
            return Setting::where('key', 'like', 'mail_%')->pluck('value', 'key');
        });
         $globalSettings = Cache::get('global_settings');
         if (!$globalSettings) {
             Log::warning('Global settings cache missed, recaching now for report sending.');
             $globalSettings = Cache::rememberForever('global_settings', function () {
                 return Setting::pluck('value', 'key');
             });
         }

        $recipientEmail = $globalSettings->get('report_recipient_email');

        // Validar configuración y destinatario
        if (empty($mailSettings->get('mail_username')) || empty($mailSettings->get('mail_password')) || empty($mailSettings->get('mail_mailer'))) {
            Log::error('Configuración de correo incompleta al intentar enviar reporte.', $mailSettings->toArray());
            Cache::forget('mail_settings');
            return back()->with('error', 'Error: La configuración de correo (Gmail) no está completa. Revisa Usuario, Contraseña de App y Mailer en Configuración.');
        }
         if (empty($recipientEmail)) {
             Log::warning('Correo destinatario para reportes no configurado.');
            return back()->with('error', 'Error: No se ha configurado un correo electrónico para recibir los reportes en Configuración.');
        }
        Log::info('Configuración de correo y destinatario (' . $recipientEmail . ') validados.'); // <-- Log 3


        // 3. Obtener los datos del reporte
        Log::info('Obteniendo datos del reporte...'); // <-- Log 4
        $productSales = Sale::whereBetween('created_at', [$startDate, $endDate])->with('products')->orderBy('created_at', 'desc')->get();
        $totalProductSalesAmount = $productSales->sum('total_amount');
        $totalProductSalesCount = $productSales->count();
        $membershipPayments = Payment::whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])->with(['member', 'subscription.membershipType'])->orderBy('created_at', 'desc')->get();
        $totalMembershipPaymentsAmount = $membershipPayments->sum('amount');
        $totalMembershipPaymentsCount = $membershipPayments->count();
        $cashMovements = CashMovement::whereBetween('created_at', [$startDate, $endDate])->with('user')->orderBy('created_at', 'desc')->get();
        $totalCashEntries = $cashMovements->where('type', 'entry')->sum('amount');
        $totalCashExits = $cashMovements->where('type', 'exit')->sum('amount');
        $netCashMovement = $totalCashEntries - $totalCashExits;
        $grandTotal = $totalProductSalesAmount + $totalMembershipPaymentsAmount + $netCashMovement;

        // Agrupar todos los datos necesarios para el Mailable en un array
        $reportData = compact(
            'startDate', 'endDate', 'productSales', 'totalProductSalesAmount', 'totalProductSalesCount',
            'membershipPayments', 'totalMembershipPaymentsAmount', 'totalMembershipPaymentsCount',
            'cashMovements', 'totalCashEntries', 'totalCashExits', 'netCashMovement', 'grandTotal'
        );
        Log::info('Datos del reporte recopilados.'); // <-- Log 5

        // 4. Configurar y Enviar Correo usando Mailer dinámico
        try {
            Log::info('Preparando configuración dinámica del Mailer...'); // <-- Log 6
            // Prepara la configuración específica para este envío
            $mailConfig = [
                'transport' => $mailSettings->get('mail_mailer'),
                'host' => $mailSettings->get('mail_host'),
                'port' => $mailSettings->get('mail_port'),
                'encryption' => $mailSettings->get('mail_encryption'),
                'username' => $mailSettings->get('mail_username'),
                'password' => $mailSettings->get('mail_password'),
                'timeout' => null,
                'local_domain' => env('MAIL_EHLO_DOMAIN'), // Opcional, desde .env
            ];
            $fromAddress = $mailSettings->get('mail_from_address');
            $fromName = $mailSettings->get('mail_from_name') ?? $globalSettings->get('gym_name', config('app.name'));

            // Crear instancia de Mailer con configuración dinámica
            $mailer = app()->makeWith('mailer', ['name' => 'report_smtp']); // Nombre temporal
            $transport = app('mail.manager')->createSymfonyTransport($mailConfig); // Crea el transportador
            $dynamicMailer = new Mailer('report_smtp', app('view'), $transport, app('events')); // Crea el Mailer
            $dynamicMailer->alwaysFrom($fromAddress, $fromName); // Establece el remitente
            Log::info('Mailer dinámico creado.'); // <-- Log 7

            // Enviar el correo usando el Mailable SalesReportMail
            Log::info('Intentando enviar correo a: ' . $recipientEmail); // <-- Log 8
            $dynamicMailer->to($recipientEmail)
                          ->send(new SalesReportMail($reportData, $fromAddress, $fromName));
            Log::info('Correo de reporte enviado exitosamente (comando send ejecutado).'); // <-- Log 9

        } catch (Exception $e) {
            Log::error('FALLO al enviar el correo del reporte.', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]); // <-- Log de Error Detallado
            Cache::forget('mail_settings'); // Limpiar caché si falló la conexión
            report($e); // Registrar el error completo en logs
            return back()->with('error', 'Error al enviar el correo del reporte: ' . $e->getMessage());
        }

        // Redirige de vuelta con mensaje de éxito
        return back()->with('success', 'Reporte de caja enviado exitosamente a ' . $recipientEmail);
    }
}