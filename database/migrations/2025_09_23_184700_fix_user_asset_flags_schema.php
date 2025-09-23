<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            // Ajusta tipos das colunas para o esperado no SQL Server
            // code: NVARCHAR(50) NOT NULL
            try { DB::statement("ALTER TABLE user_asset_flags ALTER COLUMN code NVARCHAR(50) NOT NULL"); } catch (\Throwable $e) {}
            // no_buy: BIT NOT NULL (mantém valor existente)
            try { DB::statement("ALTER TABLE user_asset_flags ALTER COLUMN no_buy BIT NOT NULL"); } catch (\Throwable $e) {}
            // created_at/updated_at: DATETIME2(3) NULL
            try { DB::statement("ALTER TABLE user_asset_flags ALTER COLUMN created_at DATETIME2(3) NULL"); } catch (\Throwable $e) {}
            try { DB::statement("ALTER TABLE user_asset_flags ALTER COLUMN updated_at DATETIME2(3) NULL"); } catch (\Throwable $e) {}
        }
        // Para outros drivers, assumimos que a migration de criação já definiu os tipos corretos.
    }

    public function down(): void
    {
        // Sem rollback específico; não é seguro inferir tipos anteriores.
    }
};
