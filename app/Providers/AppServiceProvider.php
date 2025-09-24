<?php

namespace App\Providers;

use App\Models\TareaServicio;
use Illuminate\Support\ServiceProvider;
use App\Observers\TareaServicioObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TareaServicio::observe(TareaServicioObserver::class);
    }
}