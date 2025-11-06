<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Task;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Task::create(['title' => 'Comprar toallas de papel para el baño']);
        Task::create(['title' => 'Llamar al técnico de la caminadora 3']);
        Task::create(['title' => 'Revisar inventario de suplementos', 'is_completed' => true]);
    }
}