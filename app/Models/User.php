<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Spatie\Permission\Traits\HasRoles; // <--- Importamos Spatie

class User extends Authenticatable // implements MustVerifyEmail
{
    // AQUI ESTÁ LA MAGIA: Agregamos HasRoles al núcleo del usuario
    use HasFactory, Notifiable, HasRoles; 

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Lo dejamos por si lo ocupas de respaldo, aunque Spatie usa sus propias tablas
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => AsArrayObject::class,
        ];
    }

    // --- FUNCIONES DE AYUDA (Ahora respaldadas por el motor de Spatie) ---
    
    /**
     * Verifica si el usuario es Superadmin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin'); // Este hasRole ahora viene del paquete Spatie
    }

    /**
     * Verifica si el usuario es Admin o Superadmin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('superadmin');
    }
}