<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PixPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'PIX - LISTAR',
            'PIX - INCLUIR',
            'PIX - EDITAR',
            'PIX - VER',
            'PIX - EXCLUIR',
            'PIX - EXPORTAR',
        ];

        foreach ($perms as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Atribui ao papel admin, se existir
        if ($admin = Role::where('name', 'admin')->first()) {
            $admin->givePermissionTo($perms);
        }
    }
}
