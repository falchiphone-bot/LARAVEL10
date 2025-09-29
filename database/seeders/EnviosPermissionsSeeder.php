<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class EnviosPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissoes = [
            'ENVIOS - LISTAR',
            'ENVIOS - INCLUIR',
            'ENVIOS - EDITAR',
            'ENVIOS - VER',
            'ENVIOS - EXCLUIR',
            // Permissões específicas para custos de envio
            'ENVIOS - CUSTOS - LISTAR',
            'ENVIOS - CUSTOS - INCLUIR',
            'ENVIOS - CUSTOS - EDITAR',
            'ENVIOS - CUSTOS - EXCLUIR',
        ];
        foreach ($permissoes as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }
    }
}
