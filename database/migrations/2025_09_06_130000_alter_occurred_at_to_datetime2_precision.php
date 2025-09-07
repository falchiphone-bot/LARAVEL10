<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') { return; }
        // Garantir que a coluna exista
        $col = DB::selectOne("SELECT DATA_TYPE, IS_NULLABLE, DATETIME_PRECISION FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='openai_chat_records' AND COLUMN_NAME='occurred_at'");
        if (!$col) { return; }
        // Se já for datetime2 com precisão >= 3 não fazemos nada
        $dataType = strtolower($col->DATA_TYPE);
        $precision = (int)($col->DATETIME_PRECISION ?? 0);
        if ($dataType === 'datetime2' && $precision >= 3) { return; }

        // Tornar nulos temporariamente para evitar falha se existir linha nula
        $nullable = strtoupper($col->IS_NULLABLE ?? 'NO') === 'YES';

        // Escolhemos precisão 3 (milissegundos) suficiente para logs e evita excesso de espaço.
        // Se houver NULL e a coluna for NOT NULL, podemos forçar '2000-01-01' antes.
        if (!$nullable) {
            DB::statement("UPDATE openai_chat_records SET occurred_at = '2000-01-01T00:00:00' WHERE occurred_at IS NULL");
        }

        // Alterar tipo explicitamente.
        try {
            DB::statement("ALTER TABLE openai_chat_records ALTER COLUMN occurred_at datetime2(3) " . ($nullable ? 'NULL' : 'NOT NULL'));
        } catch (Throwable $e) {
            // fallback: tentar datetime2 sem precisão
            try { DB::statement("ALTER TABLE openai_chat_records ALTER COLUMN occurred_at datetime2 " . ($nullable ? 'NULL' : 'NOT NULL')); } catch (Throwable $e2) {}
        }
    }

    public function down(): void
    {
        // Não reverte precisão (sem necessidade). Poderia voltar para datetime simples se requerido.
    }
};
