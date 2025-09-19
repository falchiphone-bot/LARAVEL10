<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class FormaPagamentoPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'FORMA_PAGAMENTOS - LISTAR',
            'FORMA_PAGAMENTOS - INCLUIR',
            'FORMA_PAGAMENTOS - EDITAR',
            'FORMA_PAGAMENTOS - VER',
            'FORMA_PAGAMENTOS - EXCLUIR',
            'FORMA_PAGAMENTOS - EXPORTAR',
        ];
        foreach ($perms as $p) {
            Permission::findOrCreate($p);
        }
    }
}
