<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saf_campeonatos', function (Blueprint $table) {
            $table->unsignedBigInteger('federacao_id')->nullable()->after('pais');
            $table->foreign('federacao_id')->references('id')->on('saf_federacoes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('saf_campeonatos', function (Blueprint $table) {
            $table->dropForeign(['federacao_id']);
            $table->dropColumn('federacao_id');
        });
    }
};
