<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('openai_chat_records')) {
            return;
        }
        // Detectar tipo atual da coluna occurred_at no SQL Server (ou ignorar se não for sqlsrv)
        $driver = DB::getDriverName();
        if ($driver !== 'sqlsrv') {
            return; // apenas relevante para SQL Server na situação reportada
        }
        $row = DB::selectOne("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'openai_chat_records' AND COLUMN_NAME='occurred_at'");
        $type = $row->DATA_TYPE ?? null;
        if ($type && in_array(strtolower($type), ['datetime', 'datetime2', 'smalldatetime'])) {
            // já é datetime, nada a fazer
            return;
        }

        // Se chegou aqui provavelmente é nvarchar / varchar armazenando 'dd/mm/yyyy ...'
        // 1. Criar coluna temporária datetime2
        Schema::table('openai_chat_records', function (Blueprint $table) {
            $table->dateTime('occurred_at_tmp')->nullable();
        });

        // 2. Preencher usando estilo 103 (dd/mm/yyyy) + tempo
        // TRY_CONVERT evita falha em strings inválidas; permanecerão NULL e poderão ser tratadas manualmente.
        DB::statement("UPDATE openai_chat_records SET occurred_at_tmp = TRY_CONVERT(datetime2, occurred_at, 103)");

        // 3. Remover coluna antiga (texto)
        Schema::table('openai_chat_records', function (Blueprint $table) {
            $table->dropColumn('occurred_at');
        });

        // 4. Criar coluna correta datetime (pode ser nullable inicialmente para receber dados) e migrar
        Schema::table('openai_chat_records', function (Blueprint $table) {
            $table->dateTime('occurred_at')->nullable();
        });

        DB::statement("UPDATE openai_chat_records SET occurred_at = occurred_at_tmp");

        // 5. Remover temporária
        Schema::table('openai_chat_records', function (Blueprint $table) {
            $table->dropColumn('occurred_at_tmp');
        });

        // 6. Opcional: tornar NOT NULL onde possível
        DB::statement("UPDATE openai_chat_records SET occurred_at = '2000-01-01 00:00:00' WHERE occurred_at IS NULL");
        // Se desejar NOT NULL:
        try {
            Schema::table('openai_chat_records', function (Blueprint $table) {
                $table->dateTime('occurred_at')->nullable(false)->change();
            });
        } catch (Throwable $e) {
            // Ignorar se o driver não suportar change() ou se houver linhas ainda NULL.
        }

        // 7. Índice auxiliar (se já não existir)
        try {
            Schema::table('openai_chat_records', function (Blueprint $table) {
                $table->index(['occurred_at']);
            });
        } catch (Throwable $e) {
            // índice possivelmente já existe
        }
    }

    public function down(): void
    {
        // Reverter para NVARCHAR não é desejável; deixamos vazio intencionalmente.
    }
};
