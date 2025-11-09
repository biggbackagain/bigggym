<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\CashMovement;
use App\Models\Setting;
use Carbon\Carbon; // <-- Asegúrate que Carbon esté importado
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
     *
     * ***** ESTE MÉTODO DEBE DEFINIR $startDate y $endDate *****
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
        // Si no hay fecha, usa el día de hoy
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

        // 3. PASAR LAS VARIABLES CLAVE A LA VISTA
        return view('sales-report.index', compact(
            'startDate', // <-- ¡VARIABLE REQUERIDA POR LA VISTA!
            'endDate',   // <-- VARIABLE REQUERIDA POR LA VISTA!
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
        // ... (el código para sendEmailReport es largo y está bien,
        // solo asegúrate de que esté completo en tu archivo) ...

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

        // 2. Obtener la configuración de correo y el destinatario
        $mailSettings = Cache::remember('mail_settings', 60*60, function () { /* ... */ });
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
             Log::warning('Report recipient email is not configured.');
            return back()->with('error', 'Error: No se ha configurado un correo electrónico para recibir los reportes en Configuración.');
        }


        // 3. Obtener los datos del reporte
        Log::info('Obteniendo datos del reporte...');
        $productSales = Sale::whereNull('deleted_at')
                           ->whereBetween('created_at', [$startDate, $endDate])
                           ->with('products')->orderBy('created_at', 'desc')->get();
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

        // 4. Configurar y Enviar Correo usando Mailer dinámico
        try {
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

            // Enviar el correo usando el Mailable SalesReportMail
            $dynamicMailer->to($recipientEmail)
                          ->send(new SalesReportMail($reportData, $fromAddress, $fromName));

            Log::info('Sales report email sent successfully to: ' . $recipientEmail);

        } catch (Exception $e) {
            Log::error('Failed to send sales report email.', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Cache::forget('mail_settings'); // Limpiar caché si falló la conexión
            report($e); // Registrar el error completo en logs
            return back()->with('error', 'Error al enviar el correo del reporte: ' . $e->getMessage());
        }

        // Redirige de vuelta con mensaje de éxito
        return back()->with('success', 'Reporte de caja enviado exitosamente a ' . $recipientEmail);
    }
}