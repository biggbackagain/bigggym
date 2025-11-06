<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\MembershipTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\CashMovementController;
// QUITA: use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// --- Rutas Protegidas ---
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Módulo de Miembros (Quitamos middleware de renew)
    Route::resource('members', MemberController::class);
    Route::get('members/{member}/renew', [MemberController::class, 'showRenewForm'])->name('members.renew'); // Sin middleware
    Route::post('members/{member}/renew', [MemberController::class, 'processRenewal'])->name('members.processRenewal'); // Sin middleware

    // Módulo de Check-in
    Route::get('check-in', [CheckInController::class, 'index'])->name('check-in.index');
    Route::post('check-in', [CheckInController::class, 'store'])->name('check-in.store');

    // Módulo de Administración de Tarifas (Quitamos middleware)
    Route::resource('membership-types', MembershipTypeController::class)
        ->names('admin.memberships');
        // ->middleware('can:manage-tariffs'); // <-- Eliminado

    // Módulo de Configuración (Quitamos middleware)
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index'); // Sin middleware
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update'); // Sin middleware

    // Módulo de Tareas
    Route::resource('tasks', TaskController::class)->only(['store', 'update', 'destroy']);

    // Módulo de Correos Masivos (Quitamos middleware)
    Route::get('mail', [MailController::class, 'index'])->name('mail.index'); // Sin middleware
    Route::post('mail', [MailController::class, 'send'])->name('mail.send'); // Sin middleware

    // Módulo de Productos (Quitamos middleware)
    Route::resource('products', ProductController::class); // Sin middleware

    // Módulo de Inventario (Quitamos middleware)
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index'); // Sin middleware
    Route::post('inventory', [InventoryController::class, 'update'])->name('inventory.update'); // Sin middleware

    // Módulo de Punto de Venta (POS)
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('pos', [PosController::class, 'store'])->name('pos.store');

    // Módulo de Reporte de Caja (Quitamos middleware)
    Route::get('sales-report', [SalesReportController::class, 'index'])->name('sales.report'); // Sin middleware
    Route::post('sales-report/email', [SalesReportController::class, 'sendEmailReport'])->name('sales.report.email'); // Sin middleware

    // Rutas de Movimientos de Caja (Quitamos middleware)
    Route::get('cash-movements', [CashMovementController::class, 'index'])->name('cash.index'); // Sin middleware
    Route::post('cash-movements', [CashMovementController::class, 'store'])->name('cash.store'); // Sin middleware

    // --- Rutas de Gestión de Usuarios ELIMINADAS ---
    // Route::resource('users', UserController::class) ...

});

// Rutas de Autenticación
require __DIR__.'/auth.php';