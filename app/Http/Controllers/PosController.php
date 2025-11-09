<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\SaleReceiptMail;
use App\Models\Setting;
use Illuminate\Mail\Mailer;
use Carbon\Carbon;

class PosController extends Controller
{
    /**
     * Muestra la interfaz del Punto de Venta (POS).
     */
    public function index(Request $request)
    {
        $query = Product::query()->where('is_active', true)->where('stock', '>', 0);

        if ($request->filled('search')) { /* ... */ }

        $products = $query->orderBy('name')->get();
        return view('pos.index', compact('products'));
    }

    /**
     * Almacena la venta y redirige a la vista del ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'card'])],
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $cartItems = $validated['cart'];
        $paymentMethod = $validated['payment_method'];
        $paymentReference = ($paymentMethod === 'cash') ? null : ($validated['payment_reference'] ?? null);
        $totalAmount = 0;
        $pivotData = [];
        $sale = null;

        try {
            DB::transaction(function () use ($cartItems, $paymentMethod, $paymentReference, &$totalAmount, &$pivotData, &$sale) {

                foreach ($cartItems as $item) {
                    $product = Product::lockForUpdate()->find($item['id']);
                    $quantity = $item['quantity'];

                    if (!$product || $product->stock < $quantity) {
                        throw new Exception("Stock insuficiente para: {$product->name}. Solo quedan {$product->stock}.");
                    }

                    $product->decrement('stock', $quantity);
                    $itemTotal = $product->price * $quantity;
                    $totalAmount += $itemTotal;
                    $pivotData[$product->id] = [
                        'quantity' => $quantity,
                        'price_at_sale' => $product->price
                    ];
                }

                // Crear la Venta
                $sale = Sale::create([
                    'user_id' => Auth::id(),
                    'total_amount' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'payment_reference' => $paymentReference,
                ]);

                // Adjuntar productos
                $sale->products()->attach($pivotData);
            });

        } catch (Exception $e) {
            return redirect()->route('pos.index')->with('error', $e->getMessage());
        }

        // Redirigir a la vista del ticket
        session()->flash('success', '¡Venta registrada exitosamente!');
        return redirect()->route('pos.receipt', $sale->id);
    }

    /**
     * Muestra la página del ticket/comprobante de venta (para reimprimir).
     */
    public function showReceipt(Sale $sale)
    {
        $sale->load('products', 'user');
        return view('pos.receipt', compact('sale'));
    }

    /**
     * Envía el comprobante de venta por correo.
     */
    public function emailReceipt(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'email' => 'required|email'
        ]);

        $sale->load('products');

        try {
            $mailSettings = Cache::remember('mail_settings', 60*60, function () { return Setting::where('key', 'like', 'mail_%')->pluck('value', 'key'); });
            $globalSettings = Cache::get('global_settings');

            if (empty($mailSettings->get('mail_username')) || empty($mailSettings->get('mail_password'))) {
                return back()->with('error', 'Error: La configuración de correo no está completa.');
            }
            
            $mailConfig = [
                'transport' => $mailSettings->get('mail_mailer'),
                'host' => $mailSettings->get('mail_host'),
                'port' => $mailSettings->get('mail_port'),
                'encryption' => $mailSettings->get('mail_encryption'),
                'username' => $mailSettings->get('mail_username'),
                'password' => $mailSettings->get('mail_password'),
                'timeout' => null,
            ];
            $fromAddress = $mailSettings->get('mail_from_address');
            $fromName = $mailSettings->get('mail_from_name') ?? $globalSettings->get('gym_name', config('app.name'));

            $mailer = app()->makeWith('mailer', ['name' => 'receipt_smtp']);
            $transport = app('mail.manager')->createSymfonyTransport($mailConfig);
            $dynamicMailer = new Mailer('receipt_smtp', app('view'), $transport, app('events'));
            $dynamicMailer->alwaysFrom($fromAddress, $fromName);

            $dynamicMailer->to($validated['email'])
                          ->send(new SaleReceiptMail($sale, $fromAddress, $fromName));

        } catch (Exception $e) {
            report($e);
            return back()->with('error', 'Error al enviar el correo: ' . $e->getMessage());
        }

        return back()->with('success', 'Comprobante enviado exitosamente a ' . $validated['email']);
    }
}