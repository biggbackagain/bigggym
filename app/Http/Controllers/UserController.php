<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller; // Asegúrate que use este
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate; // Importar Gate
use Illuminate\Support\Facades\Log; // Importar Log

class UserController extends Controller
{
    // Roles que se pueden asignar
    protected $assignableRoles = ['admin', 'receptionist'];

    // Permisos disponibles
    protected $availablePermissions = [
        'access_settings' => 'Acceder a Configuración',
        'manage_products' => 'Gestionar Productos',
        'manage_inventory' => 'Gestionar Inventario',
        'view_reports' => 'Ver Reportes',
        'send_bulk_mail' => 'Enviar Correos Masivos',
        'manage_tariffs' => 'Gestionar Tarifas',
        'manage_cash_movements' => 'Gestionar Movimientos Caja',
        'renew_memberships' => 'Renovar Membresías',
    ];

    /**
     * Constructor - SIN MIDDLEWARE.
     */
    public function __construct()
    {
        // El middleware se aplica en routes/web.php
        // $this->middleware('can:manage-users'); // <-- ELIMINADO DE AQUÍ
    }

    /**
     * Muestra la lista de usuarios.
     */
    public function index()
    {
        // La verificación 'can:manage-users' ya ocurrió en la ruta
        $currentUser = Auth::user();
        $query = User::query();

        // El Superadmin ve a todos
        if (!$currentUser->isSuperAdmin()) {
            $query->where('role', '!=', 'superadmin');
        }
        $users = $query->orderBy('name')->paginate(15);
        return view('users.index', compact('users'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create()
    {
        $roles = array_merge($this->assignableRoles, ['admin', 'superadmin']);
        $permissions = $this->availablePermissions;
        return view('users.create', compact('roles', 'permissions'));
    }

    /**
     * Guarda un nuevo usuario en la base de datos.
     */
    public function store(Request $request)
    {
        $validRoles = array_merge($this->assignableRoles, ['admin', 'superadmin']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
            'role' => ['required', Rule::in($validRoles)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['boolean'],
        ]);

        $permissionsData = [];
        if (!empty($validated['permissions'])) {
            foreach ($this->availablePermissions as $key => $label) {
                if (isset($validated['permissions'][$key])) { $permissionsData[$key] = true; }
            }
        }
        if ($validated['role'] === 'admin' || $validated['role'] === 'superadmin') { $permissionsData = null; }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'permissions' => !empty($permissionsData) ? $permissionsData : null,
            'email_verified_at' => now(),
        ]);
        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un usuario.
     */
    public function edit(User $user)
    {
        $currentUser = Auth::user();
        
        // (La verificación de si eres superadmin ya la hizo el middleware)

        $roles = array_merge($this->assignableRoles, ['admin', 'superadmin']);
        $permissions = $this->availablePermissions;
        // Deshabilitar campo rol si se edita a sí mismo
        $disableRole = ($user->id === $currentUser->id);
        return view('users.edit', compact('user', 'roles', 'permissions', 'disableRole'));
    }

    /**
     * Actualiza un usuario existente.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();
        $validRoles = array_merge($this->assignableRoles, ['admin', 'superadmin']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', Password::min(8), 'confirmed'],
            'role' => ['required', Rule::in($validRoles)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['boolean'],
        ]);

        if ($user->id === $currentUser->id && $validated['role'] !== 'superadmin') {
             return back()->withErrors(['role' => 'No puedes quitarte el rol de Super Administrador.'])->withInput();
        }

        $permissionsData = [];
        if (!empty($validated['permissions'])) {
             foreach ($this->availablePermissions as $key => $label) {
                if (isset($validated['permissions'][$key])) { $permissionsData[$key] = true; }
            }
        }
        if ($validated['role'] === 'admin' || $validated['role'] === 'superadmin') { $permissionsData = null; }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!($user->id === $currentUser->id)) { $user->role = $validated['role']; }
        $user->permissions = !empty($permissionsData) ? $permissionsData : null;
        if (!empty($validated['password'])) { $user->password = Hash::make($validated['password']); }
        $user->save();
        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina un usuario.
     */
    public function destroy(User $user)
    {
         $currentUser = Auth::user();
        if ($user->isSuperAdmin()) { return redirect()->route('users.index')->with('error', 'El Super Administrador no puede ser eliminado.'); }
        if ($user->id === $currentUser->id) { return redirect()->route('users.index')->with('error', 'No puedes eliminar tu propia cuenta.'); }
        $userName = $user->name;
        $user->delete();
        return redirect()->route('users.index')->with('success', "Usuario '{$userName}' eliminado exitosamente.");
    }
}