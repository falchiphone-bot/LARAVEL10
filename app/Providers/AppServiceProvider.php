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

        // Controle de HTTPS via variável de ambiente (sem forçar por padrão)
        // Defina FORCE_HTTPS=true no .env para obrigar geração de URLs https.
        // Mantemos isso desligado por padrão para evitar timeouts quando 443 não estiver disponível.
        $forceHttps = filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOL);
        if ($forceHttps) {
            URL::forceScheme('https');
        }
    }
}
