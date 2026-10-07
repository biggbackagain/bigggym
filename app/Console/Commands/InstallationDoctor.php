<?php

namespace App\Console\Commands;

use App\Services\BiometricClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InstallationDoctor extends Command
{
    protected $signature = 'app:doctor {--biometrics : Comprueba también la conexión al motor facial}';
    protected $description = 'Comprueba los requisitos de instalación sin modificar datos';

    public function handle(BiometricClient $biometrics): int
    {
        $failed = false;
        $check = function (bool $ok, string $label) use (&$failed) {
            $this->line(($ok ? '[OK] ' : '[ERROR] ').$label);
            $failed = $failed || !$ok;
        };
        $check(PHP_VERSION_ID >= 80400, 'PHP 8.4 o posterior');
        foreach (['pdo_mysql', 'mbstring', 'openssl', 'fileinfo', 'curl', 'dom', 'xml', 'zip'] as $extension) {
            $check(extension_loaded($extension), 'Extensión '.$extension);
        }
        $check(!empty(config('app.key')), 'APP_KEY configurada');
        $check(is_file(public_path('build/manifest.json')), 'Recursos compilados (public/build)');
        $check(!is_file(public_path('hot')), 'Sin referencia a un servidor Vite de desarrollo');
        foreach ([storage_path(), base_path('bootstrap/cache')] as $path) {
            $check(is_writable($path), 'Directorio escribible: '.$path);
        }
        $check(is_dir(public_path('storage')), 'Fotografías disponibles en public/storage');
        try {
            DB::connection()->getPdo();
            $check(true, 'Conexión a la base de datos');
        } catch (\Throwable $e) {
            $check(false, 'Conexión a la base de datos: revisa DB_* y el servicio MySQL');
        }
        if (app()->environment('production')) {
            $check(!config('app.debug'), 'APP_DEBUG desactivado');
            $check(str_starts_with(config('app.url'), 'https://'), 'APP_URL usa HTTPS');
            $check((bool) config('session.secure'), 'Cookie de sesión segura');
        }
        if (!config('services.biometrics.enabled')) {
            $this->info('Reconocimiento desactivado. El acceso por código sigue disponible.');
        } else {
            try {
                $connection = $biometrics->connection();
                $check(true, 'Configuración del reconocimiento');
                if ($this->option('biometrics')) {
                    $response = $connection->get('/health');
                    $check($response->successful() && $response->json('status') === 'ok', 'Conexión autenticada al reconocimiento');
                }
            } catch (\Throwable $e) {
                $check(false, 'Reconocimiento: revisa BIOMETRICS_URL, BIOMETRICS_TOKEN y el servicio');
            }
        }
        $this->line('Verifica también el programador (schedule:run cada minuto), SMTP y mysqldump para respaldos.');
        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
