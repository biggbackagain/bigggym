<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Task;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Import Storage

class DashboardController extends Controller
{
    public function index()
    {
        // Carga settings directamente desde la BD para esta vista específica
        // Esto asegura que siempre tenga los datos más frescos al cargar el dashboard
        $settings = Setting::pluck('value', 'key');

        // Carga tareas
        $tasks = Task::orderBy('is_completed')->orderBy('created_at', 'desc')->get();

        // Calcula conteos de miembros
        $activeMembersCount = Member::active()->count();
        $inactiveMembersCount = Member::inactive()->count();
        $totalMembersCount = $activeMembersCount + $inactiveMembersCount;

        // Pasa todos los datos a la vista
        return view('dashboard', compact(
            'settings', // Usa esta variable en la vista
            'tasks',
            'activeMembersCount',
            'inactiveMembersCount',
            'totalMembersCount'
        ));
    }
}