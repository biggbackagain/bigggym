<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(['key' => 'gym_name'], ['value' => 'Nombre de tu Gimnasio']);
        Setting::updateOrCreate(['key' => 'gym_logo'], ['value' => null]);
        Setting::updateOrCreate(['key' => 'gym_main_image'], ['value' => null]);
        Setting::updateOrCreate(['key' => 'member_code_prefix'], ['value' => 'GYM-']);
        Setting::updateOrCreate(['key' => 'app_timezone'], ['value' => 'America/Mexico_City']); // Valor por defecto

        // Configuración de correo
        Setting::updateOrCreate(['key' => 'mail_mailer'], ['value' => 'smtp']);
        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => 'smtp.gmail.com']);
        Setting::updateOrCreate(['key' => 'mail_port'], ['value' => '465']);
        Setting::updateOrCreate(['key' => 'mail_username'], ['value' => '']); // Tu correo de Gmail
        Setting::updateOrCreate(['key' => 'mail_password'], ['value' => '']); // Tu clave de app de 16 letras
        Setting::updateOrCreate(['key' => 'mail_encryption'], ['value' => 'ssl']);
        Setting::updateOrCreate(['key' => 'mail_from_address'], ['value' => '']); // Se llenará con el username
        Setting::updateOrCreate(['key' => 'mail_from_name'], ['value' => 'Gimnasio']);

        // Correo para recibir reportes
        Setting::updateOrCreate(['key' => 'report_recipient_email'], ['value' => '']); // Correo destino para reportes
    }
}