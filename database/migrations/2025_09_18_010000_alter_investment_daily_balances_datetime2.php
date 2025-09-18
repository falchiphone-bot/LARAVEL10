<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Só aplica se driver for SQL Server
        if (DB::getDriverName() !== 'sqlsrv') return;
        // Alterar snapshot_at, created_at, updated_at para datetime2(7)
        DB::statement("ALTER TABLE investment_daily_balances ALTER COLUMN snapshot_at DATETIME2(7) NOT NULL");
        DB::statement("ALTER TABLE investment_daily_balances ALTER COLUMN created_at DATETIME2(7) NOT NULL");
        DB::statement("ALTER TABLE investment_daily_balances ALTER COLUMN updated_at DATETIME2(7) NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') return;
        // Reverter para DATETIME padrão (sem fração) – ajuste conforme estado original
        DB::statement("ALTER TABLE investment_daily_balances ALTER COLUMN snapshot_at DATETIME NOT NULL");
        DB::statement("ALTER TABLE investment_daily_balances ALTER COLUMN created_at DATETIME NOT NULL");
        DB::statement("ALTER TABLE investment_daily_balances ALTER COLUMN updated_at DATETIME NOT NULL");
    }
};
