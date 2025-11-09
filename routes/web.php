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
use App\Http\Controllers\BackupController;
use App\Http\Controllers\MyReportController; // Incluye el controlador de "Mi Corte"
// UserController ya no se usa

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// --- Rutas Protegidas (Requieren inicio de sesión, pero SIN permisos de rol) ---
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Módulo de Miembros
    Route::resource('members', MemberController::class);
    Route::get('members/{member}/renew', [MemberController::class, 'showRenewForm'])->name('members.renew');
    Route::post('members/{member}/renew', [MemberController::class, 'processRenewal'])->name('members.processRenewal');

    // Módulo de Check-in
    Route::get('check-in', [CheckInController::class, 'index'])->name('check-in.index');
    Route::post('check-in', [CheckInController::class, 'store'])->name('check-in.store');

    // Módulo de Administración de Tarifas
    Route::resource('membership-types', MembershipTypeController::class)
        ->names('admin.memberships');

    // Módulo de Configuración
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');

    // Módulo de Tareas
    Route::resource('tasks', TaskController::class)->only(['store', 'update', 'destroy']);

    // Módulo de Correos Masivos
    Route::get('mail', [MailController::class, 'index'])->name('mail.index');
    Route::post('mail', [MailController::class, 'send'])->name('mail.send');

    // Módulo de Productos
    Route::resource('products', ProductController::class);

    // Módulo de Inventario
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory', [InventoryController::class, 'update'])->name('inventory.update');

    // Módulo de Punto de Venta (POS)
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('pos', [PosController::class, 'store'])->name('pos.store');

    // Módulo de Reporte de Caja (Final)
    Route::get('sales-report', [SalesReportController::class, 'index'])->name('sales.report');
    Route::post('sales-report/email', [SalesReportController::class, 'sendEmailReport'])->name('sales.report.email');

    // Módulo de Mi Corte
    Route::get('my-report', [MyReportController::class, 'index'])->name('my.report');

    // Rutas de Movimientos de Caja
    Route::get('cash-movements', [CashMovementController::class, 'index'])->name('cash.index');
    Route::post('cash-movements', [CashMovementController::class, 'store'])->name('cash.store');
           
    // Rutas de Backups
    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/create', [BackupController::class, 'create'])->name('create');
        Route::get('/download/{filename}', [BackupController::class, 'download'])
            ->name('download')
            ->where('filename', '.*');
        Route::delete('/delete/{filename}', [BackupController::class, 'delete'])
            ->name('delete')
            ->where('filename', '.*');
    });

    // Rutas de Gestión de Usuarios ELIMINADAS

});

// Rutas de Autenticación
require __DIR__.'/auth.php';