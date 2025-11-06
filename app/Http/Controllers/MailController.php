<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Member;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Mail\GymPromotionMail;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Arr;

class MailController extends Controller
{
    /**
     * Muestra el formulario para enviar correos.
     */
    public function index()
    {
        return view('mail.index');
    }

    /**
     * Procesa y envía los correos.
     */
    public function send(Request $request)
    {
        $request->validate([
            'target' => 'required|in:all,active,inactive',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // 1. Obtener la configuración de correo de la BD
        $settings = Cache::remember('mail_settings', 60*60, function () {
            return Setting::where('key', 'like', 'mail_%')->pluck('value', 'key');
        });

        // 2. Validar que la configuración exista
        if (empty($settings['mail_username']) || empty($settings['mail_password']) || empty($settings['mail_mailer'])) {
            Cache::forget('mail_settings');
            return back()->with('error', 'Error: La configuración de correo (usuario, contraseña o mailer) no está completa o es inválida. Revisa la Configuración.');
        }

        // 3. Preparar la configuración dinámica
        $config = [
            'transport' => $settings['mail_mailer'],
            'host' => $settings['mail_host'],
            'port' => $settings['mail_port'],
            'encryption' => $settings['mail_encryption'],
            'username' => $settings['mail_username'],
            'password' => $settings['mail_password'],
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ];

        $fromAddress = $settings['mail_from_address'];
        $fromName = $settings['mail_from_name'] ?? 'Tu Gimnasio';

        // 4. Obtener los destinatarios
        $query = Member::query();
        if ($request->target == 'active') {
            $query->active();
        } elseif ($request->target == 'inactive') {
            $query->inactive();
        }

        $members = $query->whereNotNull('email')->where('email', '!=', '')->get();

        if ($members->isEmpty()) {
            return back()->with('error', 'No se encontraron miembros con correos válidos para este filtro.');
        }

        // 5. Enviar los correos usando un Mailer configurado dinámicamente
        try {
            // Crear el transportador y el Mailer dinámico
            $mailer = app()->makeWith('mailer', ['name' => 'dynamic_smtp']);
            $transport = app('mail.manager')->createSymfonyTransport($config);
            $dynamicMailer = new Mailer('dynamic_smtp', app('view'), $transport, app('events'));
            $dynamicMailer->alwaysFrom($fromAddress, $fromName);


            foreach ($members as $member) { // <-- Iteramos sobre los miembros
                $dynamicMailer->to($member->email)
                              // Pasamos el objeto $member completo al constructor del Mailable
                              ->send(new GymPromotionMail($request->subject, $request->message, $fromAddress, $fromName, $member)); // <-- PASAR $member
            }
        } catch (\Exception $e) {
            Cache::forget('mail_settings');
            report($e);
            return back()->with('error', 'Error al enviar correos: ' . $e->getMessage());
        }

        return back()->with('success', '¡Correos enviados exitosamente a ' . $members->count() . ' miembros!');
    }
}