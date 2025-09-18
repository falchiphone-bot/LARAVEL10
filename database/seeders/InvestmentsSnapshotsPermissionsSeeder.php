<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InvestmentsSnapshotsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'INVESTIMENTOS SNAPSHOTS - LISTAR',
            'INVESTIMENTOS SNAPSHOTS - CRIAR',
            'INVESTIMENTOS SNAPSHOTS - EXCLUIR',
            'INVESTIMENTOS SNAPSHOTS - EXPORTAR',
            'INVESTIMENTOS SNAPSHOTS - RESTAURAR',
        ];
        foreach ($perms as $name) {
            Permission::firstOrCreate(['name'=>$name]);
        }
        if ($admin = Role::where('name','admin')->first()) {
            $admin->givePermissionTo($perms);
        }
    }
}
