<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'membership_type_id',
        'payment_id',
        'start_date',
        'end_date',
        'payment_method',
        'payment_reference', // <--- AGREGADO
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function membershipType(): BelongsTo { return $this->belongsTo(MembershipType::class); }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
}