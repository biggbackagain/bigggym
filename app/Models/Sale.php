<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Sale extends Model
{
    use HasFactory, SoftDeletes; // <-- Añadir SoftDeletes

    protected $fillable = [
        'total_amount',
        'payment_method',
        'payment_reference',
        'user_id', // <-- Campo para seguir quién vendió
        'member_id', // <-- Si lo agregamos, debe ir aquí.
    ];

    /**
     * Hook del modelo que se ejecuta al iniciar.
     */
    protected static function booted(): void
    {
        // Define el evento 'deleting' (al cancelar venta)
        static::deleting(function (Sale $sale) {
            if ($sale->forceDeleting) {
                Log::warning("Borrando permanentemente la venta #{$sale->id}. No se restaurará el stock.");
                return;
            }

            Log::info("Cancelando (Soft Delete) Venta #{$sale->id}. Restaurando stock...");
            
            // Restaurar el stock de los productos
            $sale->products->each(function ($product) {
                $quantityToRestore = $product->pivot->quantity;
                $product->increment('stock', $quantityToRestore);
            });
        });
    }

    /**
     * Los productos que pertenecen a esta venta.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('quantity', 'price_at_sale')
            ->withTimestamps();
    }

    /**
     * El usuario (cajero) que realizó la venta.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}