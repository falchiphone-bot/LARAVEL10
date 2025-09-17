<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') { return; }

        $cols = DB::select("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, DATETIME_PRECISION FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='saf_clubes' AND COLUMN_NAME IN ('created_at','updated_at')");
        foreach ($cols as $col) {
            $name = $col->COLUMN_NAME;
            $isNullable = strtoupper($col->IS_NULLABLE ?? 'YES') === 'YES';
            $dataType = strtolower($col->DATA_TYPE ?? '');
            $precision = (int)($col->DATETIME_PRECISION ?? 0);
            if (!($dataType === 'datetime2' && $precision === 7)) {
                try {
                    DB::statement("ALTER TABLE saf_clubes ALTER COLUMN {$name} datetime2(7) ".($isNullable ? 'NULL' : 'NOT NULL'));
                } catch (\Throwable $e) {
                    // Fallback sem precisão explícita
                    try { DB::statement("ALTER TABLE saf_clubes ALTER COLUMN {$name} datetime2 ".($isNullable ? 'NULL' : 'NOT NULL')); } catch (\Throwable $e2) {}
                }
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') { return; }

        $cols = DB::select("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, DATETIME_PRECISION FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='saf_clubes' AND COLUMN_NAME IN ('created_at','updated_at')");
        foreach ($cols as $col) {
            $name = $col->COLUMN_NAME;
            $isNullable = strtoupper($col->IS_NULLABLE ?? 'YES') === 'YES';
            $dataType = strtolower($col->DATA_TYPE ?? '');
            $precision = (int)($col->DATETIME_PRECISION ?? 0);
            if ($dataType === 'datetime2' && $precision === 7) {
                try { DB::statement("ALTER TABLE saf_clubes ALTER COLUMN {$name} datetime2(3) ".($isNullable ? 'NULL' : 'NOT NULL')); } catch (\Throwable $e) {}
            }
        }
    }
};
