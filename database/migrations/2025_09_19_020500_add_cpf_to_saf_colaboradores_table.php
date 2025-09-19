<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('saf_colaboradores') && !Schema::hasColumn('saf_colaboradores', 'cpf')) {
            Schema::table('saf_colaboradores', function (Blueprint $table) {
                $table->string('cpf', 20)->nullable()->after('documento');
                $table->index('cpf', 'idx_colab_cpf');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('saf_colaboradores') && Schema::hasColumn('saf_colaboradores', 'cpf')) {
            Schema::table('saf_colaboradores', function (Blueprint $table) {
                $table->dropIndex('idx_colab_cpf');
                $table->dropColumn('cpf');
            });
        }
    }
};
