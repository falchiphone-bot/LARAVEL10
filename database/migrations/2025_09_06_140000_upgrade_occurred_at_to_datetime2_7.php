<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') { return; }
        $col = DB::selectOne("SELECT DATA_TYPE, IS_NULLABLE, DATETIME_PRECISION FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='openai_chat_records' AND COLUMN_NAME='occurred_at'");
        if(!$col) return;
        $dataType = strtolower($col->DATA_TYPE ?? '');
        $precision = (int)($col->DATETIME_PRECISION ?? 0);
        if($dataType === 'datetime2' && $precision === 7){
            return; // já está no alvo
        }
        $nullable = strtoupper($col->IS_NULLABLE ?? 'NO') === 'YES';
        // Alterar para datetime2(7)
        try {
            DB::statement("ALTER TABLE openai_chat_records ALTER COLUMN occurred_at datetime2(7) ".($nullable? 'NULL':'NOT NULL'));
        } catch (Throwable $e) {
            // fallback: tentar sem precisão explícita e depois re-alterar
            try { DB::statement("ALTER TABLE openai_chat_records ALTER COLUMN occurred_at datetime2 ".($nullable? 'NULL':'NOT NULL')); } catch (Throwable $e2) {}
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') { return; }
        // Reverter para datetime2(3) apenas se atualmente for 7
        $col = DB::selectOne("SELECT DATA_TYPE, DATETIME_PRECISION, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='openai_chat_records' AND COLUMN_NAME='occurred_at'");
        if(!$col) return;
        $dataType = strtolower($col->DATA_TYPE ?? '');
        $precision = (int)($col->DATETIME_PRECISION ?? 0);
        if($dataType === 'datetime2' && $precision === 7){
            $nullable = strtoupper($col->IS_NULLABLE ?? 'NO') === 'YES';
            try { DB::statement("ALTER TABLE openai_chat_records ALTER COLUMN occurred_at datetime2(3) ".($nullable? 'NULL':'NOT NULL')); } catch (Throwable $e) {}
        }
    }
};
