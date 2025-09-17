<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FuncaoProfissionalPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'FUNCAOPROFISSIONAL - LISTAR',
            'FUNCAOPROFISSIONAL - INCLUIR',
            'FUNCAOPROFISSIONAL - EDITAR',
            'FUNCAOPROFISSIONAL - VER',
            'FUNCAOPROFISSIONAL - EXCLUIR',
        ];
        foreach ($perms as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($perms);
        }
    }
}
