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
