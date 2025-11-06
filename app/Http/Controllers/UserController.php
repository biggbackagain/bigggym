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
use Illuminate\Support\Facades\Gate; // <-- Importar Gate
use Illuminate\Support\Facades\Log; // <-- Importar Log

class UserController extends Controller // Asegúrate que extienda Controller
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
     * Constructor - SIN MIDDLEWARE TEMPORALMENTE.
     */
    public function __construct()
    {
        // $this->middleware('can:manage-users'); // <-- COMENTADO O ELIMINADO PARA LA PRUEBA
    }

    /**
     * Muestra la lista de usuarios.
     */
    public function index()
    {
        // --- VERIFICACIÓN MANUAL DEL GATE ---
        // Llama explícitamente al Gate 'manage-users'
        if (!Gate::allows('manage-users')) {
            // Si el Gate devuelve false, registra el rol y aborta con 403
            Log::warning('[UserController@index] Manual Gate check FAILED for user ID: ' . Auth::id() . ' | Role: ' . Auth::user()->role);
            abort(403, 'Acción no autorizada (Verificación manual fallida).');
        } else {
             // Si el Gate devuelve true, registra que pasó
             Log::info('[UserController@index] Manual Gate check PASSED for user ID: ' . Auth::id() . ' | Role: ' . Auth::user()->role);
        }
        // --- FIN VERIFICACIÓN MANUAL ---

        // El resto de la lógica para mostrar la lista
        $currentUser = Auth::user();
        $query = User::query();
        if (!$currentUser->isSuperAdmin()) {
            $query->where('role', '!=', 'superadmin');
        }
        $users = $query->orderBy('name')->paginate(15);
        return view('users.index', compact('users'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     * (Añadir verificación manual aquí también por seguridad)
     */
    public function create()
    {
        if (!Gate::allows('manage-users')) { abort(403); } // Verificación manual

        $roles = array_merge($this->assignableRoles, ['admin', 'superadmin']);
        $permissions = $this->availablePermissions;
        return view('users.create', compact('roles', 'permissions'));
    }

    /**
     * Guarda un nuevo usuario en la base de datos.
     * (Añadir verificación manual)
     */
    public function store(Request $request)
    {
        if (!Gate::allows('manage-users')) { abort(403); } // Verificación manual

        $validRoles = array_merge($this->assignableRoles, ['admin', 'superadmin']);
        $validated = $request->validate([ /* ... reglas ... */ ]);
        $permissionsData = [];
        if (!empty($validated['permissions'])) { /* ... procesar ... */ }
        if ($validated['role'] === 'admin' || $validated['role'] === 'superadmin') { $permissionsData = null; }

        User::create([ /* ... datos ... */ ]);
        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un usuario.
     * (Añadir verificación manual)
     */
    public function edit(User $user)
    {
        if (!Gate::allows('manage-users')) { abort(403); } // Verificación manual

        $currentUser = Auth::user();
        // Superadmin no puede editar a otro Superadmin? Lo permitimos aquí.
        // $disableRole solo aplica si se edita a sí mismo
        $roles = array_merge($this->assignableRoles, ['admin', 'superadmin']);
        $permissions = $this->availablePermissions;
        $disableRole = ($user->id === $currentUser->id);
        return view('users.edit', compact('user', 'roles', 'permissions', 'disableRole'));
    }

    /**
     * Actualiza un usuario existente.
     * (Añadir verificación manual)
     */
    public function update(Request $request, User $user)
    {
        if (!Gate::allows('manage-users')) { abort(403); } // Verificación manual

        $currentUser = Auth::user();
        $validRoles = array_merge($this->assignableRoles, ['admin', 'superadmin']);
        $validated = $request->validate([ /* ... reglas ... */ ]);
        if ($user->id === $currentUser->id && $validated['role'] !== 'superadmin') { /* ... error ... */ }
        $permissionsData = [];
        if (!empty($validated['permissions'])) { /* ... procesar ... */ }
        if ($validated['role'] === 'admin' || $validated['role'] === 'superadmin') { $permissionsData = null; }

        // ... (actualizar datos usuario) ...
        $user->save();
        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina un usuario.
     * (Añadir verificación manual)
     */
    public function destroy(User $user)
    {
         if (!Gate::allows('manage-users')) { abort(403); } // Verificación manual

         $currentUser = Auth::user();
        if ($user->isSuperAdmin()) { return redirect()->route('users.index')->with('error', 'El Super Administrador no puede ser eliminado.'); }
        if ($user->id === $currentUser->id) { return redirect()->route('users.index')->with('error', 'No puedes eliminar tu propia cuenta.'); }
        $userName = $user->name;
        $user->delete();
        return redirect()->route('users.index')->with('success', "Usuario '{$userName}' eliminado exitosamente.");
    }
}