<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // <-- AÑADIR IMPORT

class PosController extends Controller
{
    /**
     * Muestra la interfaz del Punto de Venta (POS).
     */
    public function index(Request $request)
    {
        $query = Product::query()->where('is_active', true)->where('stock', '>', 0);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->get();
        return view('pos.index', compact('products'));
    }

    /**
     * Almacena la venta y descuenta el inventario.
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

        try {
            DB::transaction(function () use ($cartItems, $paymentMethod, $paymentReference, &$totalAmount, &$pivotData) {

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

                // Crear la Venta CON los datos de pago y el ID del usuario
                $sale = Sale::create([
                    'user_id' => Auth::id(), // <-- AÑADIR ID DE USUARIO LOGUEADO
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

        return redirect()->route('pos.index')->with('success', '¡Venta registrada exitosamente! Total: $' . number_format($totalAmount, 2) . ' (' . ucfirst($paymentMethod) . ')');
    }
}