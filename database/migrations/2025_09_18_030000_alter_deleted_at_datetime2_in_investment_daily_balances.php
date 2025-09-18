<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Apenas para SQL Server
        if (DB::getDriverName() !== 'sqlsrv') { return; }
        // Verifica se coluna existe
        if (!Schema::hasColumn('investment_daily_balances','deleted_at')) { return; }
        // Altera precisão para alinhar com snapshot_at/created_at/updated_at
        DB::statement("ALTER TABLE investment_daily_balances ALTER COLUMN deleted_at DATETIME2(7) NULL");
    }
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') { return; }
        if (!Schema::hasColumn('investment_daily_balances','deleted_at')) { return; }
        // Reverte para DATETIME padrão (se desejar)
        DB::statement("ALTER TABLE investment_daily_balances ALTER COLUMN deleted_at DATETIME NULL");
    }
};
