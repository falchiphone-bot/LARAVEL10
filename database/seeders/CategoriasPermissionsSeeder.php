<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CategoriasPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'CATEGORIAS - LISTAR',
            'CATEGORIAS - INCLUIR',
            'CATEGORIAS - EDITAR',
            'CATEGORIAS - VER',
            'CATEGORIAS - EXCLUIR',
            'CATEGORIAS - EXPORTAR',
        ];
        foreach ($perms as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
        if ($role = Role::where('name','admin')->first()) {
            $role->givePermissionTo($perms);
        }
    }
}
