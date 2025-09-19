<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MarketPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perm = 'MERCADO - VER STATUS';
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);

        // Concede automaticamente para o papel admin, se existir
        if ($admin = Role::where('name', 'admin')->first()) {
            $admin->givePermissionTo($perm);
        }
    }
}
