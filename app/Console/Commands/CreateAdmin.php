<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';
    protected $description = 'Crea el primer administrador sin credenciales predeterminadas';

    public function handle(): int
    {
        if (User::exists()) {
            $this->error('Ya existen usuarios. Adminístralos desde la aplicación; este comando es solo para la primera instalación.');
            return self::FAILURE;
        }
        $data = [
            'name' => $this->ask('Nombre del administrador'),
            'email' => $this->ask('Correo electrónico'),
            'password' => $this->secret('Contraseña (mínimo 12 caracteres)'),
            'password_confirmation' => $this->secret('Confirma la contraseña'),
        ];
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:12|confirmed',
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) $this->error($message);
            return self::FAILURE;
        }
        DB::transaction(function () use ($data) {
            foreach (['superadmin', 'admin', 'receptionist'] as $role) Role::findOrCreate($role, 'web');
            $user = User::create([
                'name' => $data['name'], 'email' => $data['email'],
                'password' => $data['password'], 'role' => 'superadmin',
            ]);
            $user->assignRole('superadmin');
        });
        $this->info('Administrador creado. Inicia sesión y configura las tarifas y los datos del gimnasio.');
        return self::SUCCESS;
    }
}
