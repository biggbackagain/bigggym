<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateExpiredMemberships extends Command
{
    // IMPORTANTE: Este es el nombre del comando que busca Artisan
    protected $signature = 'members:expire';

    // Descripción de lo que hace
    protected $description = 'Revisa las membresías y cambia a inactivo a los usuarios expirados';

    public function handle()
    {
        $today = Carbon::today();
        $this->info("📅 Revisando con fecha de hoy: " . $today->toDateString());
        
        // Buscamos sin importar si está en mayúscula, minúscula o inglés
        $members = Member::whereIn('status', ['Activo', 'activo', 'Active', 'active'])->get(); 
        
        $this->info("👥 Encontré " . $members->count() . " miembros marcados como activos en la BD.");
        $updatedCount = 0;

        foreach ($members as $member) {
            // Buscamos su última suscripción
            $latestSubscription = $member->subscriptions()->latest('end_date')->first();

            if (!$latestSubscription) {
                $this->warn("⚠️ El miembro {$member->name} (ID: {$member->id}) NO tiene suscripciones registradas.");
                continue;
            }

            $endDate = Carbon::parse($latestSubscription->end_date)->startOfDay();
            
            // Si la fecha de fin es menor a hoy (ayer o antes)
            if ($endDate->lt($today)) {
                $this->info("🔴 Desactivando a {$member->name}. Venció el: {$endDate->toDateString()}");
                
                $member->status = 'expired'; // Ojo: asegúrate de que sea la palabra correcta para tu sistema
                $member->save();
                $updatedCount++;
            } else {
                // Descomenta la siguiente línea si quieres ver a los que SÍ están vigentes
                // $this->line("✅ {$member->name} sigue vigente hasta {$endDate->toDateString()}");
            }
        }

        $this->info("🏁 Resumen: Se han desactivado {$updatedCount} miembros expirados.");
    }
}