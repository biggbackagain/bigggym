<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use Carbon\Carbon;

class CheckMemberships extends Command
{
    /**
     * El nombre y la firma del comando (así lo llamaremos en la terminal).
     */
    protected $signature = 'members:check-status';

    /**
     * La descripción que aparecerá en la lista de comandos.
     */
    protected $description = 'Verifica las suscripciones y cambia el estado de los miembros vencidos a expirado.';

    /**
     * Ejecuta el comando en la base de datos.
     */
    public function handle()
    {
        // 1. Buscamos a todos los miembros que actualmente están activos
        $activeMembers = Member::where('status', 'active')->with('latestSubscription')->get();
        $expiredCount = 0;

        // 2. Revisamos uno por uno
        foreach ($activeMembers as $member) {
            // Extraemos su última suscripción
            $subscription = $member->latestSubscription;

            // Si tiene suscripción y la fecha de fin es anterior a hoy (isPast)
            if ($subscription && Carbon::parse($subscription->end_date)->isPast()) {
                // Le bajamos el switch a expirado
                $member->update(['status' => 'expired']);
                $expiredCount++;
            }
        }

        // 3. Imprimimos un pequeño reporte en la consola (opcional)
        $this->info("Revision completa: Se actualizaron {$expiredCount} miembros a estado expirado.");
    }
}