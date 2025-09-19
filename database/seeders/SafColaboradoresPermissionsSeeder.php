<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SafColaboradoresPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'SAF_COLABORADORES - LISTAR',
            'SAF_COLABORADORES - INCLUIR',
            'SAF_COLABORADORES - EDITAR',
            'SAF_COLABORADORES - VER',
            'SAF_COLABORADORES - EXCLUIR',
            'SAF_COLABORADORES - EXPORTAR',
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
    }
}
