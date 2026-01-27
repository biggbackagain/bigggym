<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Exception;
use Carbon\Carbon;
use ZipArchive; 

class BackupController extends Controller
{
    private $diskName = 'backups_local'; 

    public function index()
    {
        $disk = Storage::disk($this->diskName);
        
        if (!$disk->exists('')) {
            $disk->makeDirectory('');
        }
        
        $files = $disk->allFiles();

        $backups = collect($files)
            ->filter(function ($file) {
                return \Illuminate\Support\Str::endsWith($file, '.zip');
            })
            ->map(function ($file) use ($disk) {
                return [
                    'name' => $file,
                    'size' => $this->formatBytes($disk->size($file)),
                    'date' => Carbon::createFromTimestamp($disk->lastModified($file))
                                    ->tz(config('app.timezone')),
                ];
            })
            ->sortByDesc('date')
            ->values();

        return view('backups.index', compact('backups'));
    }

    public function create()
    {
        try {
            Log::info('[BackupController] Iniciando backup manual...');
            $exitCode = Artisan::call('backup:run', ['--only-files' => false, '--only-db' => false]);
            
            if ($exitCode === 0) {
                Log::info('[BackupController] Backup creado exitosamente.');
                Artisan::call('backup:clean'); 
                return redirect()->route('backups.index')->with('success', '¡Nuevo backup creado exitosamente!');
            } else {
                $output = Artisan::output();
                Log::error('[BackupController] Error al crear backup: ' . $output);
                return redirect()->route('backups.index')->with('error', 'Error al crear el backup. Revisa los logs.');
            }

        } catch (Exception $e) {
            report($e);
            return redirect()->route('backups.index')->with('error', 'Error crítico: ' . $e->getMessage());
        }
    }

    /**
     * IMPORTAR UN BACKUP EXTERNO
     */
    public function upload(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:512000', // Máx 500MB (ajusta si necesitas más)
        ]);

        try {
            $file = $request->file('backup_file');
            $filename = $file->getClientOriginalName(); // Usamos el nombre original

            // Guardamos el archivo en el disco de backups
            Storage::disk($this->diskName)->putFileAs('', $file, $filename);

            return redirect()->route('backups.index')->with('success', 'Backup importado correctamente. Ahora puedes restaurarlo desde la lista.');

        } catch (Exception $e) {
            Log::error("Error subiendo backup: " . $e->getMessage());
            return redirect()->route('backups.index')->with('error', 'Error al subir el archivo: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        if (!Storage::disk($this->diskName)->exists($filename)) {
            abort(404, 'Archivo no encontrado.');
        }

        return Storage::disk($this->diskName)->download($filename);
    }

    public function delete($filename)
    {
        $disk = Storage::disk($this->diskName);

        if ($disk->exists($filename)) {
            $disk->delete($filename);
            return redirect()->route('backups.index')->with('success', 'Backup eliminado.');
        }

        return redirect()->route('backups.index')->with('error', 'El archivo no existe.');
    }

    public function restore($filename)
    {
        $disk = Storage::disk($this->diskName);

        if (!$disk->exists($filename)) {
            return redirect()->route('backups.index')->with('error', 'El archivo de respaldo no existe.');
        }

        $fullPath = $disk->path($filename); 
        $tempDir = storage_path('app/backup-temp/' . now()->timestamp); 

        try {
            set_time_limit(300); 

            $zip = new ZipArchive;
            if ($zip->open($fullPath) === TRUE) {
                $zip->extractTo($tempDir);
                $zip->close();
            } else {
                return redirect()->route('backups.index')->with('error', 'No se pudo abrir el archivo ZIP.');
            }

            $sqlFiles = File::allFiles($tempDir);
            $sqlFileToRestore = null;

            foreach ($sqlFiles as $file) {
                if ($file->getExtension() === 'sql') {
                    $sqlFileToRestore = $file->getRealPath();
                    break;
                }
            }

            if (!$sqlFileToRestore) {
                File::deleteDirectory($tempDir); 
                return redirect()->route('backups.index')->with('error', 'El respaldo no contiene un archivo SQL válido.');
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $sql = file_get_contents($sqlFileToRestore);
            DB::unprepared($sql);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            File::deleteDirectory($tempDir);

            Log::info("[BackupController] Restauración exitosa desde: {$filename}");

            return redirect()->route('backups.index')->with('success', '¡Sistema restaurado exitosamente! Por favor, verifica los datos.');

        } catch (Exception $e) {
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::error("[BackupController] Fallo en restauración: " . $e->getMessage());
            return redirect()->route('backups.index')->with('error', 'Error crítico al restaurar: ' . $e->getMessage());
        }
    }

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