<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        // Traemos todos los usuarios junto con sus roles
        $users = User::with('roles')->get();
        return view('users.index', compact('users'));
    }

    public function edit(User $user)
    {
        // Traemos todos los roles disponibles (superadmin, admin, recepcionista)
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name'
        ]);

        // 1. Actualizamos sus datos básicos
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // 2. MAGIA DE SPATIE: Le quitamos roles viejos y le asignamos el nuevo
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        // Candado de seguridad: No puedes borrarte a ti mismo
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Bloqueo de seguridad: No puedes eliminar tu propia cuenta activa.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado del sistema.');
    }
}