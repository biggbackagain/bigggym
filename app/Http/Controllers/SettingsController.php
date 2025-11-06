<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use DateTimeZone; // Asegúrate que este 'use' esté al inicio

class SettingsController extends Controller
{
    /**
     * Muestra el formulario de configuración.
     */
    public function index()
    {
        $settings = Setting::pluck('value', 'key');
        // Obtener lista de zonas horarias para el dropdown (limitado a América)
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::AMERICA); // Puedes quitar ::AMERICA si quieres todas
        return view('settings.index', compact('settings', 'timezones'));
    }

    /**
     * Actualiza la configuración.
     */
    public function update(Request $request)
    {
        // Obtener zonas horarias válidas para validación
        $validTimezones = DateTimeZone::listIdentifiers(DateTimeZone::AMERICA); // O quitar ::AMERICA

        // Validar los datos del formulario
        $request->validate([
            'gym_name' => 'required|string|max:255',
            'gym_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', // Valida tipo y tamaño
            'gym_main_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4096', // Valida tipo y tamaño
            'member_code_prefix' => 'nullable|string|max:10',
            'app_timezone' => 'required|string|in:' . implode(',', $validTimezones), // Valida que sea una zona válida

            // Validación para correo de reporte
            'report_recipient_email' => 'nullable|email|max:255',

            // Validación de correo para envío
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|email',
            'mail_password' => 'required|string', // Es la contraseña de aplicación
            'mail_encryption' => 'required|string',
            'mail_from_name' => 'required|string|max:255',
        ]);

        // Actualiza o crea cada configuración en la base de datos
        Setting::updateOrCreate(['key' => 'gym_name'], ['value' => $request->gym_name]);
        Setting::updateOrCreate(['key' => 'member_code_prefix'], ['value' => $request->member_code_prefix]);
        Setting::updateOrCreate(['key' => 'app_timezone'], ['value' => $request->app_timezone]);
        Setting::updateOrCreate(['key' => 'report_recipient_email'], ['value' => $request->report_recipient_email]); // Guarda correo reporte

        // Configuración de correo
        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => $request->mail_host]);
        Setting::updateOrCreate(['key' => 'mail_port'], ['value' => $request->mail_port]);
        Setting::updateOrCreate(['key' => 'mail_username'], ['value' => $request->mail_username]);
        Setting::updateOrCreate(['key' => 'mail_password'], ['value' => $request->mail_password]); // Guarda la contraseña de app
        Setting::updateOrCreate(['key' => 'mail_encryption'], ['value' => $request->mail_encryption]);
        Setting::updateOrCreate(['key' => 'mail_from_name'], ['value' => $request->mail_from_name]);
        Setting::updateOrCreate(['key' => 'mail_from_address'], ['value' => $request->mail_username]); // El 'from' es el mismo que el username para Gmail
        Setting::updateOrCreate(['key' => 'mail_mailer'], ['value' => 'smtp']); // Fija el mailer a smtp

        // Manejo de subida del Logo
        if ($request->hasFile('gym_logo')) {
            $oldLogoPath = Setting::where('key', 'gym_logo')->value('value'); // Obtiene ruta relativa antigua
            // Guarda en 'storage/app/private/public/settings' y obtiene ruta relativa nueva
            $newLogoPath = $request->file('gym_logo')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'gym_logo'], ['value' => $newLogoPath]); // Guarda nueva ruta
            // Si había una ruta antigua y es diferente, borra el archivo antiguo
            if ($oldLogoPath && $oldLogoPath !== $newLogoPath) {
                Storage::disk('public')->delete($oldLogoPath);
            }
        }

        // Manejo de subida de Imagen Principal (misma lógica que el logo)
        if ($request->hasFile('gym_main_image')) {
            $oldImagePath = Setting::where('key', 'gym_main_image')->value('value');
            $newImagePath = $request->file('gym_main_image')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'gym_main_image'], ['value' => $newImagePath]);
            if ($oldImagePath && $oldImagePath !== $newImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }

        // Limpiar cachés relevantes
        Cache::forget('global_settings');
        Cache::forget('mail_settings');
        Cache::forget('app_timezone');

        // Actualizar config de PHP y Laravel en tiempo real para esta petición
        config(['app.timezone' => $request->app_timezone]);
        date_default_timezone_set($request->app_timezone);

        // Redirige de vuelta con mensaje de éxito
        return redirect()->route('settings.index')->with('success', 'Configuración guardada exitosamente.');
    }
} // <-- Fin de la clase SettingsController