<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Exception;
use Carbon\Carbon; // Importar Carbon
use Illuminate\Support\Facades\Log; // Importar Log

class BackupController extends Controller
{
    private $diskName = 'backups_local'; // El disco que definimos en filesystems.php

    /**
     * Muestra la lista de backups existentes.
     */
    public function index()
    {
        $disk = Storage::disk($this->diskName);
        
        if (!$disk->exists('')) {
            $disk->makeDirectory('');
        }
        
        // Usar allFiles() para buscar en subcarpetas
        $files = $disk->allFiles();

        $backups = collect($files)
            ->filter(function ($file) {
                return \Illuminate\Support\Str::endsWith($file, '.zip');
            })
            ->map(function ($file) use ($disk) {
                return [
                    'name' => $file, // El 'name' ahora incluye la ruta (ej: 'Laravel/backup.zip')
                    'size' => $this->formatBytes($disk->size($file)),
                    'date' => Carbon::createFromTimestamp($disk->lastModified($file))
                                    ->tz(config('app.timezone')), // Corregido con zona horaria
                ];
            })
            ->sortByDesc('date')
            ->values();

        return view('backups.index', compact('backups'));
    }

    /**
     * Inicia un nuevo backup.
     */
    public function create()
    {
        try {
            Log::info('[BackupController] Iniciando backup...');
            
            $exitCode = Artisan::call('backup:run', ['--only-files' => false, '--only-db' => false]);
            
            if ($exitCode === 0) {
                Log::info('[BackupController] Backup creado exitosamente.');
                Artisan::call('backup:clean');
                Log::info('[BackupController] Limpieza de backups viejos ejecutada.');
                
                return redirect()->route('backups.index')->with('success', '¡Nuevo backup creado exitosamente!');
            } else {
                Log::error('[BackupController] Artisan::call(backup:run) falló con código: ' . $exitCode);
                $output = Artisan::output();
                Log::error('[BackupController] Output del error: ' . $output);
                return redirect()->route('backups.index')->with('error', 'Error al crear el backup. Revisa los logs. Output: ' . $output);
            }

        } catch (Exception $e) {
            report($e);
            Log::error('[BackupController] Excepción capturada: ' . $e->getMessage());
            return redirect()->route('backups.index')->with('error', 'Error al crear el backup: ' . $e->getMessage());
        }
    }

    /**
     * Descarga un archivo de backup.
     * $filename ahora puede contener slashes (ej: 'Laravel/backup.zip')
     */
    public function download($filename)
    {
        if (!Storage::disk($this->diskName)->exists($filename)) {
            abort(404, 'Archivo no encontrado.');
        }

        return Storage::disk($this->diskName)->download($filename);
    }

    /**
     * Elimina un archivo de backup.
     * $filename ahora puede contener slashes (ej: 'Laravel/backup.zip')
     */
    public function delete($filename)
    {
        $disk = Storage::disk($this->diskName);

        if ($disk->exists($filename)) {
            $disk->delete($filename);
            return redirect()->route('backups.index')->with('success', 'Backup eliminado.');
        }

        return redirect()->route('backups.index')->with('error', 'El archivo no existe.');
    }

    /**
     * Helper para formatear bytes a KB/MB/GB.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        if ($bytes == 0) return '0 B';
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}