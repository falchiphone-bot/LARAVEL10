<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SafFaixasSalariaisPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'SAF_FAIXASSALARIAIS - LISTAR',
            'SAF_FAIXASSALARIAIS - INCLUIR',
            'SAF_FAIXASSALARIAIS - EDITAR',
            'SAF_FAIXASSALARIAIS - VER',
            'SAF_FAIXASSALARIAIS - EXCLUIR',
            'SAF_FAIXASSALARIAIS - EXPORTAR',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        if ($role = Role::where('name','admin')->first()) {
            $role->givePermissionTo($perms);
        }
    }
}
