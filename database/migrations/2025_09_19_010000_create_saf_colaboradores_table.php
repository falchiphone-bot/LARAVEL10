<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('saf_colaboradores')) {
            Schema::create('saf_colaboradores', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nome', 120);

                // Relacionamentos
                $table->unsignedBigInteger('representante_id')->nullable();
                $table->unsignedBigInteger('funcao_profissional_id')->nullable();
                $table->unsignedBigInteger('saf_tipo_prestador_id')->nullable();
                $table->unsignedBigInteger('saf_faixa_salarial_id')->nullable();

                // Dados básicos
                $table->string('documento', 20)->nullable(); // CPF/CNPJ ou outro identificador
                $table->string('email', 120)->nullable();
                $table->string('telefone', 50)->nullable();
                $table->string('cidade', 120)->nullable();
                $table->string('uf', 2)->nullable();
                $table->string('pais', 60)->nullable();

                $table->boolean('ativo')->default(true);
                $table->text('observacoes')->nullable();
                $table->timestamps(7);

                // Índices
                $table->index('representante_id', 'idx_colab_representante');
                $table->index('funcao_profissional_id', 'idx_colab_funcao');
                $table->index('saf_tipo_prestador_id', 'idx_colab_tipo');
                $table->index('saf_faixa_salarial_id', 'idx_colab_faixa');
                $table->index(['cidade','uf','pais'],'idx_colab_local');
            });

            // FKs opcionais (tolerar ausência de tabela/PK no ambiente)
            try {
                Schema::table('saf_colaboradores', function (Blueprint $table) {
                    $table->foreign('representante_id')->references('id')->on('representantes')->nullOnDelete();
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('saf_colaboradores', function (Blueprint $table) {
                    $table->foreign('funcao_profissional_id')->references('id')->on('FuncaoProfissional')->nullOnDelete();
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('saf_colaboradores', function (Blueprint $table) {
                    $table->foreign('saf_tipo_prestador_id')->references('id')->on('saf_tipos_prestadores')->nullOnDelete();
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('saf_colaboradores', function (Blueprint $table) {
                    $table->foreign('saf_faixa_salarial_id')->references('id')->on('saf_faixas_salariais')->nullOnDelete();
                });
            } catch (\Throwable $e) {}

            // Regras simples (se suportado pelo banco) - não obrigatórias
            try {
                DB::statement("ALTER TABLE saf_colaboradores ADD CONSTRAINT CHK_colab_uf CHECK (uf IS NULL OR LENGTH(uf) = 2)");
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('saf_colaboradores')) {
            Schema::drop('saf_colaboradores');
        }
    }
};
