<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate; // Ya no se necesita
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// use App\Models\User; // Ya no se necesita
// use Illuminate\Support\Facades\Log; // Ya no se necesita

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // --- SE ELIMINAN TODOS LOS GATES ---
        // Gate::before(...)
        // Gate::define('manage-users', ...)
        // Gate::define('access-settings', ...)
        // ... etc ...
    }
}