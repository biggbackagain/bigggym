<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Subscription;
use App\Models\Setting;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        // 1. Estadísticas de Miembros (Variables originales)
        $activeMembersCount = Member::where('status', 'active')->count();
        $inactiveMembersCount = Member::where('status', 'expired')->count();
        $totalMembersCount = Member::count();

        // 2. RESUMEN DE CAJA (Lógica Nueva)
        $todaySubscriptions = Subscription::with('payment')
            ->whereDate('created_at', Carbon::today())
            ->get();

        $cashToday = $todaySubscriptions->where('payment_method', 'Efectivo')->sum(fn($s) => $s->payment->amount ?? 0);
        $cardToday = $todaySubscriptions->where('payment_method', 'Tarjeta')->sum(fn($s) => $s->payment->amount ?? 0);
        $transferToday = $todaySubscriptions->where('payment_method', 'Transferencia')->sum(fn($s) => $s->payment->amount ?? 0);
        $totalToday = $cashToday + $cardToday + $transferToday;

        // 3. Configuración y Tareas (Variables originales)
        $settings = Cache::remember('global_settings', 60*60, function () {
            return Setting::pluck('value', 'key');
        });
        
        $tasks = Task::orderBy('is_completed', 'asc')->latest()->get();

        return view('dashboard', compact(
            'activeMembersCount',
            'inactiveMembersCount',
            'totalMembersCount',
            'cashToday',
            'cardToday',
            'transferToday',
            'totalToday',
            'settings',
            'tasks'
        ));
    }
}