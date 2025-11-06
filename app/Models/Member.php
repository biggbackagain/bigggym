<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // <-- ¡ESTA LÍNEA ES ESENCIAL!
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class Member extends Model // <-- Ahora Laravel sabe qué 'Model' es este
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'profile_photo_path',
        'is_student',
        'status',
        'member_code',
    ];

    protected $casts = [
        'is_student' => 'boolean',
        // 'created_at' => 'datetime', // Descomenta si necesitas castear fechas
        // 'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::deleting(function ($member) {
            // Borra todas las suscripciones y pagos
            $member->subscriptions()->delete();
            $member->payments()->delete();

            // Borra la foto de perfil si existe, usando el disco 'public'
            if ($member->profile_photo_path) {
                Storage::disk('public')->delete($member->profile_photo_path);
            }
        });
    }

    /**
     * Un miembro tiene muchas suscripciones.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Obtiene la suscripción con la fecha de vencimiento más reciente.
     */
    public function latestSubscription(): HasOne
    {
        // Asegúrate que la columna sea 'end_date'
        return $this->hasOne(Subscription::class)->ofMany('end_date', 'max');
    }

    /**
     * Un miembro tiene muchos pagos.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope para miembros activos.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Scope para miembros inactivos (vencidos).
     */
    public function scopeInactive(Builder $query): void
    {
        // Asumiendo que 'expired' es el único estado inactivo
        $query->where('status', 'expired');
    }
}