<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CashEventsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'CASH EVENTS - IMPORTAR',
            'CASH EVENTS - LISTAR',
        ];
        foreach($perms as $p){
            Permission::firstOrCreate(['name'=>$p,'guard_name'=>'web']);
        }
        if($admin = Role::where('name','admin')->first()){
            $admin->givePermissionTo($perms);
        }
    }
}
