<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Aplicar apenas em SQL Server (sqlsrv)
        try {
            $driver = DB::getDriverName();
        } catch (\Throwable $e) {
            $driver = null;
        }
        if ($driver === 'sqlsrv') {
            // Remover índices dependentes antes de alterar a coluna
            DB::statement("IF EXISTS (SELECT name FROM sys.indexes WHERE name = 'asset_daily_stats_symbol_date_unique' AND object_id = OBJECT_ID('asset_daily_stats')) DROP INDEX [asset_daily_stats_symbol_date_unique] ON [asset_daily_stats]");
            DB::statement("IF EXISTS (SELECT name FROM sys.indexes WHERE name = 'asset_daily_stats_date_index' AND object_id = OBJECT_ID('asset_daily_stats')) DROP INDEX [asset_daily_stats_date_index] ON [asset_daily_stats]");
            // Alterar o tipo para DATETIME2(7)
            DB::statement('ALTER TABLE [asset_daily_stats] ALTER COLUMN [date] DATETIME2(7) NOT NULL');
            // Recriar o índice único
            DB::statement('CREATE UNIQUE INDEX [asset_daily_stats_symbol_date_unique] ON [asset_daily_stats]([symbol],[date])');
            // Recriar o índice simples em date
            DB::statement('CREATE INDEX [asset_daily_stats_date_index] ON [asset_daily_stats]([date])');
        }
    }

    public function down(): void
    {
        try {
            $driver = DB::getDriverName();
        } catch (\Throwable $e) {
            $driver = null;
        }
        if ($driver === 'sqlsrv') {
            // Remover índices antes de reverter o tipo
            DB::statement("IF EXISTS (SELECT name FROM sys.indexes WHERE name = 'asset_daily_stats_symbol_date_unique' AND object_id = OBJECT_ID('asset_daily_stats')) DROP INDEX [asset_daily_stats_symbol_date_unique] ON [asset_daily_stats]");
            DB::statement("IF EXISTS (SELECT name FROM sys.indexes WHERE name = 'asset_daily_stats_date_index' AND object_id = OBJECT_ID('asset_daily_stats')) DROP INDEX [asset_daily_stats_date_index] ON [asset_daily_stats]");
            // Reverter para DATE
            DB::statement('ALTER TABLE [asset_daily_stats] ALTER COLUMN [date] DATE NOT NULL');
            // Recriar índice único
            DB::statement('CREATE UNIQUE INDEX [asset_daily_stats_symbol_date_unique] ON [asset_daily_stats]([symbol],[date])');
            // Recriar índice simples em date
            DB::statement('CREATE INDEX [asset_daily_stats_date_index] ON [asset_daily_stats]([date])');
        }
    }
};
