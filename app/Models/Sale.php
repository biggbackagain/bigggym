<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sale extends Model
{
    use HasFactory;

    // Añadir los nuevos campos
    protected $fillable = [
        'total_amount',
        'payment_method',
        'payment_reference',
    ];

    /**
     * Los productos que pertenecen a esta venta.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('quantity', 'price_at_sale')
            ->withTimestamps(); // Mantenemos timestamps en la tabla pivote
    }
}