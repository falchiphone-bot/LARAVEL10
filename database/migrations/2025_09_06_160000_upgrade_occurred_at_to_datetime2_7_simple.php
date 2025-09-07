<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') return;
        $col = DB::selectOne("SELECT DATA_TYPE, DATETIME_PRECISION, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='openai_chat_records' AND COLUMN_NAME='occurred_at'");
        if(!$col) return;
        $type = strtolower($col->DATA_TYPE);
        $precision = (int)$col->DATETIME_PRECISION;
        $nullable = strtoupper($col->IS_NULLABLE ?? 'NO') === 'YES';
        if(!in_array($type, ['datetime','datetime2'])) return; // já convertido de texto em outra migration
        if($type === 'datetime2' && $precision === 7) return; // nada a fazer
        $nullSql = $nullable ? 'NULL':'NOT NULL';
        try {
            DB::statement("ALTER TABLE openai_chat_records ALTER COLUMN occurred_at datetime2(7) $nullSql");
        } catch (Throwable $e) {
            // Tentar remover índice composto e refazer
            try { DB::statement("DROP INDEX openai_chat_records_chat_id_occurred_at_index ON openai_chat_records"); } catch(Throwable $e2) {}
            try { DB::statement("ALTER TABLE openai_chat_records ALTER COLUMN occurred_at datetime2(7) $nullSql"); } catch(Throwable $e3) { return; }
            // Recriar índice (nome padrão do Laravel)
            try { DB::statement("CREATE INDEX openai_chat_records_chat_id_occurred_at_index ON openai_chat_records (chat_id, occurred_at)"); } catch(Throwable $e4) {}
        }
    }

    public function down(): void
    {
        // Opcional: não reduzir precisão
    }
};
