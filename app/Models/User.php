<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Support\Facades\Storage; // (No se usa aquí, pero puede ser necesario en otros)
use Illuminate\Database\Eloquent\Builder; // (No se usa aquí, pero puede ser necesario en otros)

class User extends Authenticatable // implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => AsArrayObject::class, // Castear JSON a objeto/array
        ];
    }

    // --- FUNCIONES DE ROL CORREGIDAS (CON trim/strtolower) ---
    /**
     * Verifica si el usuario tiene un rol específico (limpiando los datos).
     */
    public function hasRole(string $role): bool
    {
        // Normaliza el rol guardado y el rol solicitado
        $userRole = strtolower(trim($this->role));
        $requiredRole = strtolower(trim($role));
        return $userRole === $requiredRole;
    }

    /**
     * Verifica si el usuario es Superadmin (usando el hasRole limpio).
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Verifica si el usuario es Admin o Superadmin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->isSuperAdmin();
    }
    // --- FIN FUNCIONES CORREGIDAS ---

    /**
     * Verifica si el usuario tiene un permiso específico.
     */
    public function hasPermissionTo(string $permission): bool
    {
        // El Superadmin siempre tiene permiso (esto ahora usará el isSuperAdmin corregido)
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Si no tiene columna permissions o está vacía/null, no tiene permiso
        if (empty($this->permissions)) {
            return false;
        }

        // Verifica si el permiso existe como clave y es true en el objeto JSON
        return isset($this->permissions[$permission]) && $this->permissions[$permission] === true;
    }
}