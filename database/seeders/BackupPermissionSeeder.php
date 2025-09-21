<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class BackupPermissionSeeder extends Seeder
{
    public function run()
    {
        Permission::firstOrCreate(['name' => 'backup.executar']);
    }
}
