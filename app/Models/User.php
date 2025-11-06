<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Asegúrate que implemente MustVerifyEmail si lo necesitas
class User extends Authenticatable // implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * Originalmente solo tenía name, email, password
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // Se quitan 'role' y 'permissions'
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
            // Se quita 'permissions'
        ];
    }

    // Se quitan las funciones hasRole, isSuperAdmin, isAdmin, hasPermissionTo
}