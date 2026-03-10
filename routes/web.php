<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

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
use App\Http\Controllers\MyReportController;
use App\Http\Controllers\SalesController; 
use App\Http\Controllers\UserController; // <--- NUEVO: Controlador de Usuarios

Route::get('/', function () {
    $gymName = Cache::get('global_settings')['gym_name'] 
               ?? Setting::where('key', 'gym_name')->value('value') 
               ?? config('app.name', 'BiggGym');
    return view('welcome', compact('gymName'));
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Módulo de Usuarios y Roles (Para el Staff)
    Route::resource('users', UserController::class)->except(['show']);

    // Módulo de Miembros
    Route::resource('members', MemberController::class);
    Route::get('members/{member}/renew', [MemberController::class, 'showRenewForm'])->name('members.renew');
    Route::post('members/{member}/renew', [MemberController::class, 'processRenewal'])->name('members.processRenewal');
    Route::get('members/{member}/receipt', [MemberController::class, 'showReceipt'])->name('members.receipt');

    // Módulo de Check-in
    Route::get('check-in', [CheckInController::class, 'index'])->name('check-in.index');
    Route::post('check-in', [CheckInController::class, 'store'])->name('check-in.store');

    // Módulo de Administración de Tarifas
    Route::resource('membership-types', MembershipTypeController::class)->names('admin.memberships');

    // Módulo de Configuración
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');

    // Módulo de Tareas
    Route::resource('tasks', TaskController::class)->only(['store', 'update', 'destroy']);

    // Módulo de Correos Masivos
    Route::get('mail', [MailController::class, 'index'])->name('mail.index');
    Route::post('mail', [MailController::class, 'send'])->name('mail.send');

    // Módulo de Productos e Inventario
    Route::resource('products', ProductController::class);
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory', [InventoryController::class, 'update'])->name('inventory.update');

    // Módulo de Punto de Venta (POS)
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('pos', [PosController::class, 'store'])->name('pos.store');
    Route::get('pos/receipt/{sale}', [PosController::class, 'showReceipt'])->name('pos.receipt');
    Route::post('pos/receipt/{sale}/email', [PosController::class, 'emailReceipt'])->name('pos.receipt.email');

    // Historial de Ventas
    Route::resource('sales', SalesController::class)->only(['index', 'show', 'destroy']); 

    // Reportes
    Route::get('sales-report', [SalesReportController::class, 'index'])->name('sales.report');
    Route::post('sales-report/email', [SalesReportController::class, 'sendEmailReport'])->name('sales.report.email');
    Route::get('my-report', [MyReportController::class, 'index'])->name('my.report');

    // Movimientos de Caja
    Route::get('cash-movements', [CashMovementController::class, 'index'])->name('cash.index');
    Route::post('cash-movements', [CashMovementController::class, 'store'])->name('cash.store');

    // --- RUTAS DE BACKUPS ---
    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::get('/create', [BackupController::class, 'create'])->name('create');
        
        // NUEVA RUTA: SUBIR BACKUP EXTERNO
        Route::post('/upload', [BackupController::class, 'upload'])->name('upload');

        Route::get('/download/{filename}', [BackupController::class, 'download'])
            ->name('download')->where('filename', '.*');
            
        Route::get('/delete/{filename}', [BackupController::class, 'delete'])
            ->name('delete')->where('filename', '.*');

        Route::get('/restore/{filename}', [BackupController::class, 'restore'])
            ->name('restore')->where('filename', '.*');
    });

});

require __DIR__.'/auth.php';