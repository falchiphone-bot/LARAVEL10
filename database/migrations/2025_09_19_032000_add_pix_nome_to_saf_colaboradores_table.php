<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saf_colaboradores', function (Blueprint $table) {
            if (!Schema::hasColumn('saf_colaboradores', 'pix_nome')) {
                $table->string('pix_nome')->nullable()->after('saf_faixa_salarial_id');
            }
            // cria/garante a FK
            $table->foreign('pix_nome')->references('nome')->on('pix')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('saf_colaboradores', function (Blueprint $table) {
            // remove FK e coluna
            $table->dropForeign(['pix_nome']);
            $table->dropColumn('pix_nome');
        });
    }
};
