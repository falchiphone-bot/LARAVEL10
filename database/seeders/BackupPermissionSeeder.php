<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BackupPermissionSeeder extends Seeder
{
    public function run()
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Permissões granulares de backup
        $perms = [
            'backup.executar',
            'backup.logs.view',
            'backup.logs.download',
            'backup.logs.clear',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // Se existir papel Administrador, garante as permissões
        try {
            $adminNames = ['Administrador','admin','Admin','administrator','Administrator'];
            foreach ($adminNames as $name) {
                try {
                    $role = Role::where('name', $name)->first();
                    if ($role) {
                        $role->givePermissionTo($perms);
                    }
                } catch (\Throwable $e) {
                    // ignora se não houver role
                }
            }
        } catch (\Throwable $e) {
            // noop
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
