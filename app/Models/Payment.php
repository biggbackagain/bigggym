<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'user_id', // <-- AÑADIR ESTA LÍNEA
        'amount',
        'payment_date',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    /**
     * Un pago pertenece a un miembro.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Un pago está asociado a una suscripción.
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }
}