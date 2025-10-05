<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HoldingsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'HOLDINGS - LISTAR',      // (futuro: visualizar carteira consolidada)
            'HOLDINGS - IMPORTAR',    // importar/atualizar via Avenue Screen ou CSV
            'HOLDINGS - EXPORTAR',    // export csv/xlsx
        ];
        foreach ($perms as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        if ($admin = Role::where('name','admin')->first()) {
            $admin->givePermissionTo($perms);
        }
    }
}
