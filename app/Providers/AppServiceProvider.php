<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // <-- ¡ASEGÚRATE QUE ESTA LÍNEA ESTÉ CORRECTA!

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Compartir configuración global con todas las vistas
        try {
            // Verifica si la tabla 'settings' existe para evitar errores durante migraciones
            if (Schema::hasTable('settings')) {
                // Usar caché persistente para configuraciones generales
                $settings = Cache::rememberForever('global_settings', function () {
                    // Log para saber cuándo se reconstruye la caché (útil para depurar)
                    Log::info('Recaching global_settings from database.'); // Usa el Log importado
                    return Setting::pluck('value', 'key');
                });
                // Compartir la colección de settings con todas las vistas bajo el nombre 'globalSettings'
                View::share('globalSettings', $settings);

                // Aplicar Zona Horaria Globalmente
                // Obtener timezone de la caché/settings, o usar el default de config/app.php
                $timezone = $settings->get('app_timezone', config('app.timezone'));
                if ($timezone) {
                    // Actualizar la configuración de Laravel en tiempo de ejecución
                    config(['app.timezone' => $timezone]);
                    // Establecer la zona horaria por defecto para funciones de fecha de PHP
                    date_default_timezone_set($timezone);
                    // Opcional: Establecer locale para Carbon (formato de fechas, nombres de meses, etc.)
                    // Carbon::setLocale(config('app.locale'));
                }
            } else {
                 // Log si la tabla settings no existe aún (normal durante migrate:fresh)
                 Log::info('Settings table not found during AppServiceProvider boot.');
            }
        } catch (\Exception $e) {
             // Registrar cualquier error que ocurra durante el boot para depuración
             Log::error("Error in AppServiceProvider boot method: " . $e->getMessage()); // Usa el Log importado
             // No detener la aplicación si la base de datos no está lista
        }
    }
}