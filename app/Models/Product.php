<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // AÑADIR

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'stock',
        'sku',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'stock' => 'integer',
    ];

    /**
     * Las ventas a las que pertenece este producto.
     */
    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class)
            ->withPivot('quantity', 'price_at_sale')
            ->withTimestamps();
    }
}