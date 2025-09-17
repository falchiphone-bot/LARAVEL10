<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('saf_faixas_salariais')) {
            Schema::create('saf_faixas_salariais', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nome', 120)->nullable();

                // Contexto
                $table->unsignedBigInteger('funcao_profissional_id')->nullable();
                $table->unsignedBigInteger('saf_tipo_prestador_id')->nullable();

                // Dimensões da faixa
                $table->string('senioridade', 30)->nullable();
                $table->string('tipo_contrato', 20); // CLT, PJ, ESTAGIO
                $table->string('periodicidade', 20); // MENSAL, HORA, DIA
                $table->decimal('valor_minimo', 19, 4);
                $table->decimal('valor_maximo', 19, 4);
                $table->char('moeda', 3)->default('BRL');

                // Vigência
                $table->dateTime('vigencia_inicio', 7);
                $table->dateTime('vigencia_fim', 7)->nullable();

                $table->boolean('ativo')->default(true);
                $table->text('observacoes')->nullable();
                $table->timestamps(7);

                // Índices
                $table->index('funcao_profissional_id', 'idx_faixa_funcao');
                $table->index('saf_tipo_prestador_id', 'idx_faixa_prestador');
                $table->index(['vigencia_inicio', 'vigencia_fim'], 'idx_faixa_vigencia');
                $table->index(['moeda', 'tipo_contrato', 'senioridade'], 'idx_faixa_busca');
            });

            // FKs opcionais (tolerar ausência de tabela/PK no ambiente)
            try {
                Schema::table('saf_faixas_salariais', function (Blueprint $table) {
                    $table->foreign('funcao_profissional_id')->references('id')->on('FuncaoProfissional')->nullOnDelete();
                });
            } catch (\Throwable $e) {}
            try {
                Schema::table('saf_faixas_salariais', function (Blueprint $table) {
                    $table->foreign('saf_tipo_prestador_id')->references('id')->on('saf_tipos_prestadores')->nullOnDelete();
                });
            } catch (\Throwable $e) {}

            // CHECK constraints básicas (SQL Server)
            try {
                DB::statement("ALTER TABLE saf_faixas_salariais ADD CONSTRAINT CHK_faixa_valores CHECK (valor_minimo > 0 AND valor_maximo >= valor_minimo)");
            } catch (\Throwable $e) {}
            try {
                DB::statement("ALTER TABLE saf_faixas_salariais ADD CONSTRAINT CHK_faixa_vigencia CHECK (vigencia_fim IS NULL OR vigencia_fim >= vigencia_inicio)");
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('saf_faixas_salariais')) {
            Schema::drop('saf_faixas_salariais');
        }
    }
};
