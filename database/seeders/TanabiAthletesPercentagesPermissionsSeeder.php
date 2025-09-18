<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TanabiAthletesPercentagesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'TANABI ATLETAS PERCENTUAIS - LISTAR',
            'TANABI ATLETAS PERCENTUAIS - CRIAR',
            'TANABI ATLETAS PERCENTUAIS - EDITAR',
            'TANABI ATLETAS PERCENTUAIS - EXCLUIR',
            'TANABI ATLETAS PERCENTUAIS - EXPORTAR',
            'TANABI ATLETAS PERCENTUAIS - ADICIONAR OUTRO CLUBE',
        ];
        foreach ($perms as $name) {
            Permission::firstOrCreate(['name'=>$name]);
        }
        if ($admin = Role::where('name','admin')->first()) {
            $admin->givePermissionTo($perms);
        }
    }
}
