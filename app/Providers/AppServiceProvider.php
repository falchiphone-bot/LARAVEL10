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

        // Forçar HTTPS apenas em ambientes não-locais (ex.: produção/staging)
        // e SOMENTE quando a requisição já vier sob HTTPS (direto ou via proxy).
        // Isso evita redirecionar usuários para a porta 443 quando ela não está exposta,
        // o que causaria ERR_CONNECTION_TIMED_OUT após o login.
        if ($this->app->environment(['production', 'staging'])) {
            $isSecure = request()->isSecure();
            $forwardedProto = request()->header('X-Forwarded-Proto');
            if ($isSecure || strtolower((string) $forwardedProto) === 'https') {
                URL::forceScheme('https');
            }
        }
    }
}
