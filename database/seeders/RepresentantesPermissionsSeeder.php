<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RepresentantesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'REPRESENTANTES - LISTAR',
            'REPRESENTANTES - INCLUIR',
            'REPRESENTANTES - EDITAR',
            'REPRESENTANTES - VER',
            'REPRESENTANTES - EXCLUIR',
            'REPRESENTANTES - EXPORTAR',
        ];
        foreach ($perms as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
        if ($role = Role::where('name','admin')->first()) {
            $role->givePermissionTo($perms);
        }
    }
}
