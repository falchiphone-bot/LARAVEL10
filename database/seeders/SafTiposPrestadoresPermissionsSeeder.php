<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SafTiposPrestadoresPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'SAF_TIPOS_PRESTADORES - LISTAR',
            'SAF_TIPOS_PRESTADORES - INCLUIR',
            'SAF_TIPOS_PRESTADORES - EDITAR',
            'SAF_TIPOS_PRESTADORES - VER',
            'SAF_TIPOS_PRESTADORES - EXCLUIR',
            'SAF_TIPOS_PRESTADORES - EXPORTAR',
        ];

        foreach ($perms as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($perms);
        }
    }
}
