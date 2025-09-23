<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            try {
                if (method_exists($user, 'hasAnyRole')) {
                    return $user->hasAnyRole(['super-admin','Super-Admin','Super Admin']) ? true : null;
                }
                if (method_exists($user, 'hasRole')) {
                    return ($user->hasRole('super-admin') || $user->hasRole('Super-Admin') || $user->hasRole('Super Admin')) ? true : null;
                }
            } catch (\Throwable $e) {
                // se algo falhar, não interfere
            }
            return null;
        });

        // Gate: visualizar dashboard (Início do sistema)
        // Bloqueia usuários que possuam quaisquer permissões IRMÃOS DE EMAÚS "- LISTAR"
        Gate::define('dashboard.view', function ($user) {
            try {
                $perms = [
                    'IRMAOS_EMAUS_NOME_SERVICO - LISTAR',
                    'IRMAOS_EMAUS_NOME_PIA - LISTAR',
                    'IRMAOS_EMAUS_FICHA_CONTROLE - LISTAR',
                ];

                if (method_exists($user, 'hasAnyPermission')) {
                    $hasEmaus = $user->hasAnyPermission($perms);
                } else {
                    // fallback genérico
                    $hasEmaus = false;
                    foreach ($perms as $p) { if ($user->can($p)) { $hasEmaus = true; break; } }
                }

                return !$hasEmaus; // só pode ver o dashboard se NÃO tiver as permissões de Emaús
            } catch (\Throwable $e) {
                // Em caso de erro, não bloquear
                return true;
            }
        });

        // Abilities granulares para logs do backup FTP
        Gate::define('backup.logs.view', function ($user) {
            return $user->can('backup.logs.view') || $user->can('backup.executar');
        });
        Gate::define('backup.logs.download', function ($user) {
            return $user->can('backup.logs.download') || $user->can('backup.executar');
        });
        Gate::define('backup.logs.clear', function ($user) {
            return $user->can('backup.logs.clear') || $user->can('backup.executar');
        });
    }
}
