<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // SAF: anos padrão para facilitar seleção nos campeonatos
        $this->call([
            SafAnosSeeder::class,
            SafAnosPermissionsSeeder::class,
            SafTiposPrestadoresPermissionsSeeder::class,
            FuncaoProfissionalPermissionsSeeder::class,
            SafFaixasSalariaisPermissionsSeeder::class,
            SafColaboradoresPermissionsSeeder::class,
            PixPermissionsSeeder::class,
            MarketPermissionsSeeder::class,
            AssetDailyStatsPermissionsSeeder::class,
            HoldingsPermissionsSeeder::class,
            ProfilePermissionsSeeder::class,
            BackupPermissionSeeder::class,
            GoogleSearchPermissionsSeeder::class,
        ]);

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            OpenAIChatTypeSeeder::class,
        ]);
    }
}
