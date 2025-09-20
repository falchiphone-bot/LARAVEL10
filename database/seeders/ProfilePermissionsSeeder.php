<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProfilePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Garante que o cache de permissões seja limpo antes de semear
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Cria a permissão explicitando o guard 'web' para evitar mismatch
        $perm = Permission::firstOrCreate(
            ['name' => 'PERFIL - EXCLUIR CONTA', 'guard_name' => 'web'],
            []
        );

        // Se papéis comuns existirem, conceder automaticamente
        foreach (['Admin','admin','Super-Admin','super-admin','Super Admin','SuperAdmin'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($perm);
            }
        }

        // Limpa cache novamente após atualizações
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
