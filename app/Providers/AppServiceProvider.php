<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // Usar paginação com estilos do Bootstrap 5
        Paginator::useBootstrapFive();

        //forçar https em producao
        if ($this->app->environment('local')) {
            URL::forceScheme('https');
        }
    }
}
