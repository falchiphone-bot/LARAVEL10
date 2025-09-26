<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssetDailyStatsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'ASSET STATS - LISTAR',
            'ASSET STATS - CRIAR',
            'ASSET STATS - EDITAR',
            'ASSET STATS - EXCLUIR',
        ];
        foreach ($perms as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        if ($admin = Role::where('name', 'admin')->first()) {
            $admin->givePermissionTo($perms);
        }
    }
}
