<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            if (!Schema::hasColumn('envios','representante_id')) {
                $table->unsignedBigInteger('representante_id')->nullable()->after('user_id');
                $table->index('representante_id','idx_envios_representante');
            }
        });
        // FK separada (SQL Server pode exigir nome único)
        Schema::table('envios', function (Blueprint $table) {
            if (Schema::hasColumn('envios','representante_id')) {
                try {
                    $table->foreign('representante_id','fk_envios_representante')->references('id')->on('representantes')->nullOnDelete();
                } catch (\Throwable $e) {
                    // silencioso se já existir
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            if (Schema::hasColumn('envios','representante_id')) {
                try { $table->dropForeign('fk_envios_representante'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_envios_representante'); } catch (\Throwable $e) {}
                $table->dropColumn('representante_id');
            }
        });
    }
};
