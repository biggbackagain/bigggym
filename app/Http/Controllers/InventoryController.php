<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB; // Para usar Transacciones

class InventoryController extends Controller
{
    /**
     * Muestra la vista de ajuste de inventario.
     */
    public function index(Request $request)
    {
        $query = Product::query()->where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
        }
        
        $products = $query->orderBy('name')->paginate(20);
        return view('inventory.index', compact('products'));
    }

    /**
     * Actualiza el stock de uno o más productos.
     */
    public function update(Request $request)
    {
        // Validación: 'adjustments' debe ser un array, y cada ajuste debe tener un ID y una cantidad.
        $request->validate([
            'adjustments' => 'required|array',
            'adjustments.*.id' => 'required|integer|exists:products,id',
            'adjustments.*.quantity' => 'required|integer', // Puede ser positivo o negativo
        ]);

        try {
            // Usamos una transacción para asegurar que todo o nada se actualice
            DB::transaction(function () use ($request) {
                foreach ($request->adjustments as $adj) {
                    $product = Product::find($adj['id']);
                    if ($product) {
                        // Usamos increment() para evitar problemas de concurrencia
                        // 'increment' acepta números negativos para restar
                        $product->increment('stock', $adj['quantity']);
                    }
                }
            });
        } catch (\Exception $e) {
            return redirect()->route('inventory.index')->with('error', 'Error al actualizar el inventario: ' . $e->getMessage());
        }

        return redirect()->route('inventory.index')->with('success', 'Inventario actualizado exitosamente.');
    }
}