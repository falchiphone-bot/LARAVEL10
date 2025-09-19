<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PreparadoresPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'PREPARADORES - LISTAR',
            'PREPARADORES - INCLUIR',
            'PREPARADORES - EDITAR',
            'PREPARADORES - VER',
            'PREPARADORES - EXCLUIR',
            'PREPARADORES - EXPORTAR',
        ];
        foreach ($perms as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
        if ($role = Role::where('name','admin')->first()) {
            $role->givePermissionTo($perms);
        }
    }
}
