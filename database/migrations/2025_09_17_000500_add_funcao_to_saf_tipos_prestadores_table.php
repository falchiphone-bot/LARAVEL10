<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('saf_tipos_prestadores')) {
            Schema::table('saf_tipos_prestadores', function (Blueprint $table) {
                $table->unsignedBigInteger('funcao_profissional_id')->nullable()->after('pais');
            });

            // Adiciona a constraint separadamente para compatibilidade com SQL Server
            try {
                Schema::table('saf_tipos_prestadores', function (Blueprint $table) {
                    $table->foreign('funcao_profissional_id')
                          ->references('id')->on('FuncaoProfissional')
                          ->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // Se não conseguir criar a FK (ambiente legado), apenas segue com a coluna
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('saf_tipos_prestadores')) {
            try {
                Schema::table('saf_tipos_prestadores', function (Blueprint $table) {
                    $table->dropForeign(['funcao_profissional_id']);
                });
            } catch (\Throwable $e) {}
            Schema::table('saf_tipos_prestadores', function (Blueprint $table) {
                $table->dropColumn('funcao_profissional_id');
            });
        }
    }
};
