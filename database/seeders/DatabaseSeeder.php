<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create(); // Si quieres usuarios de prueba

        $this->call([
            MembershipTypeSeeder::class,
            SettingsSeeder::class,
            TaskSeeder::class,
            // SuperAdminSeeder::class, // <-- LÍNEA ELIMINADA O COMENTADA
        ]);
    }
}