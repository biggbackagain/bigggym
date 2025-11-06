<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MembershipType; // <-- Asegúrate de importar el modelo

class MembershipTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usamos create() para cada plan
        
        MembershipType::create([
            'name' => 'Visita Diaria',
            'duration_days' => 1,
            'price_general' => 50.00,
            'price_student' => 40.00,
        ]);

        MembershipType::create([
            'name' => 'Plan Semanal',
            'duration_days' => 7,
            'price_general' => 200.00,
            'price_student' => 180.00,
        ]);

        MembershipType::create([
            'name' => 'Mensualidad',
            'duration_days' => 30,
            'price_general' => 450.00,
            'price_student' => 400.00,
        ]);
    }
}