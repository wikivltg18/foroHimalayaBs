<?php

namespace App\Providers;

use App\Models\TareaComentario;
use App\Policies\TareaComentarioPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        TareaComentario::class => TareaComentarioPolicy::class,
        TableroServicio::class => TableroServicioPolicy::class,
        // agrega aquí otras policies si las tienes
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // (Opcional) Super admin:
        // Gate::before(fn ($user, $ability) => $user->is_admin ? true : null);
    }
}