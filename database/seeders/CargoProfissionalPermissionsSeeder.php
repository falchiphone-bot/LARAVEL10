<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CargoProfissionalPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'CARGOPROFISSIONAL - LISTAR',
            'CARGOPROFISSIONAL - INCLUIR',
            'CARGOPROFISSIONAL - EDITAR',
            'CARGOPROFISSIONAL - VER',
            'CARGOPROFISSIONAL - EXCLUIR',
            'CARGOPROFISSIONAL - EXPORTAR',
        ];
        foreach ($perms as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
        if ($role = Role::where('name','admin')->first()) {
            $role->givePermissionTo($perms);
        }
    }
}
