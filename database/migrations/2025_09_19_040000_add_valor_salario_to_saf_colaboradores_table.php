<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saf_colaboradores', function (Blueprint $table) {
            if (!Schema::hasColumn('saf_colaboradores', 'valor_salario')) {
                $table->decimal('valor_salario', 15, 2)->nullable()->after('pix_nome');
            }
        });
    }

    public function down(): void
    {
        Schema::table('saf_colaboradores', function (Blueprint $table) {
            if (Schema::hasColumn('saf_colaboradores', 'valor_salario')) {
                $table->dropColumn('valor_salario');
            }
        });
    }
};
