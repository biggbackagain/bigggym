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
        'user_id',
        'amount',
        'payment_date',
        'notes'
    ];

    /**
     * Un pago pertenece a un miembro.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Un pago tiene una suscripción asociada (donde guardamos el método).
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Un pago fue registrado por un usuario (empleado/admin).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}