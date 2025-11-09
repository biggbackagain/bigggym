<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SalesController extends Controller
{
    /**
     * Muestra la lista del historial de ventas.
     */
    public function index(Request $request)
    {
        $query = Sale::query()->with(['user', 'products'])
                     ->orderBy('created_at', 'desc'); 

        // Filtrar por ID de Venta
        if ($request->filled('search_id')) {
            $query->where('id', $request->search_id);
        }

        // Filtrar por Rango de Fechas
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();
        
        // Aplicar rango de fechas solo si no se busca por ID
        if (!$request->filled('search_id')) {
             $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Incluir canceladas si se solicita
        if ($request->get('include_canceled') == '1') {
             $query->withTrashed(); // Incluir ventas con soft delete
        } else {
             $query->whereNull('deleted_at'); // Excluir canceladas por defecto
        }

        $sales = $query->paginate(25)->appends($request->query());

        return view('sales.index', compact('sales', 'startDate', 'endDate'));
    }

    /**
     * (No usado)
     */
    public function create() { abort(404); }

    /**
     * (No usado)
     */
    public function store(Request $request) { abort(404); }

    /**
     * Redirige a la vista del ticket (pos.receipt).
     */
    public function show(Sale $sale)
    {
        return redirect()->route('pos.receipt', $sale->id);
    }

    /**
     * (No usado)
     */
    public function edit(Sale $sale) { abort(404); }

    /**
     * (No usado)
     */
    public function update(Request $request, Sale $sale) { abort(404); }

    /**
     * Cancela (Soft Delete) una venta.
     */
    public function destroy(Sale $sale)
    {
        try {
            // Llama a delete(), lo que activa el evento 'deleting' en el modelo
            // y restaura el stock.
            $sale->delete();
            Log::info("Venta #{$sale->id} cancelada exitosamente por Usuario #" . auth()->id());
            return redirect()->route('sales.index')->with('success', "Venta #{$sale->id} cancelada exitosamente. Se restauró el stock.");
        
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('sales.index')->with('error', 'Error al cancelar la venta: ' . $e->getMessage());
        }
    }
}