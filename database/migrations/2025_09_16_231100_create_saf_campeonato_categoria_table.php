<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saf_campeonato_categoria', function (Blueprint $table) {
            $table->unsignedBigInteger('campeonato_id');
            $table->unsignedBigInteger('categoria_id');

            $table->primary(['campeonato_id', 'categoria_id']);
            $table->index('categoria_id');
        });

        // FKs (tolerante caso tabela categorias já exista)
        try {
            Schema::table('saf_campeonato_categoria', function (Blueprint $table) {
                $table->foreign('campeonato_id')->references('id')->on('saf_campeonatos')->onDelete('cascade');
                $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            // ignora se o banco não suportar no momento; admin pode adicionar depois
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saf_campeonato_categoria');
    }
};
