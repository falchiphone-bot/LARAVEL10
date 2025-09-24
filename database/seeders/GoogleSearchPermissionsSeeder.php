<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GoogleSearchPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Cria a permissão se não existir
        $permName = 'GOOGLE - PESQUISA';
        $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);

        // Opcional: conceder para perfis padrão
        // Ex.: Super Admin ou um papel apropriado
        try {
            if (class_exists(Role::class)) {
                // tente achar papéis comuns
                foreach (['Super Admin', 'Administrador', 'Admin'] as $roleName) {
                    $role = Role::where('name', $roleName)->first();
                    if ($role) {
                        $role->givePermissionTo($perm);
                    }
                }
            }
        } catch (\Throwable $e) {
            // silencioso: apenas garantir a permissão criada
        }
    }
}
