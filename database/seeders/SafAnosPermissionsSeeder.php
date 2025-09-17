<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SafAnosPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'SAF_ANOS - LISTAR',
            'SAF_ANOS - INCLUIR',
            'SAF_ANOS - EDITAR',
            'SAF_ANOS - VER',
            'SAF_ANOS - EXCLUIR',
        ];

        foreach ($perms as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Tenta atribuir ao papel 'admin' se existir
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($perms);
        }
    }
}
