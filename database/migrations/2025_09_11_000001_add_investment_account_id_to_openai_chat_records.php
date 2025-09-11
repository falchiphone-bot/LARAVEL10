<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('openai_chat_records')) return;
        // 1) Adiciona a coluna (se não existir)
        if (!Schema::hasColumn('openai_chat_records', 'investment_account_id')) {
            Schema::table('openai_chat_records', function (Blueprint $table) {
                $table->unsignedBigInteger('investment_account_id')->nullable()->index();
            });
        }

        // 2) Adiciona FK com política específica por driver
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlsrv') {
            // SQL Server: evitar múltiplos caminhos em cascata -> usar NO ACTION (sem SET NULL/CASCADE)
            try {
                DB::statement("ALTER TABLE openai_chat_records WITH NOCHECK ADD CONSTRAINT openai_chat_records_investment_account_id_foreign FOREIGN KEY (investment_account_id) REFERENCES investment_accounts(id)");
            } catch (\Throwable $e) {
                // ignora se já existir
            }
        } else {
            // Outros bancos: permitir SET NULL ao apagar a conta
            Schema::table('openai_chat_records', function (Blueprint $table) {
                // Evitar duplicar FK se já existir
                $table->foreign('investment_account_id')
                    ->references('id')
                    ->on('investment_accounts')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('openai_chat_records')) return;
        // Remover FK e coluna, tolerando diferenças entre bancos
        try {
            Schema::table('openai_chat_records', function (Blueprint $table) {
                if (Schema::hasColumn('openai_chat_records', 'investment_account_id')) {
                    // Tenta remover pela API (MySQL/SQLite)
                    try { $table->dropConstrainedForeignId('investment_account_id'); } catch (\Throwable $e) {}
                    try { $table->dropForeign('openai_chat_records_investment_account_id_foreign'); } catch (\Throwable $e) {}
                }
            });
        } catch (\Throwable $e) {}

        try {
            Schema::table('openai_chat_records', function (Blueprint $table) {
                if (Schema::hasColumn('openai_chat_records', 'investment_account_id')) {
                    $table->dropColumn('investment_account_id');
                }
            });
        } catch (\Throwable $e) {}
    }
};
