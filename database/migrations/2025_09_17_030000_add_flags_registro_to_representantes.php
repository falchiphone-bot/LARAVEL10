<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('Representantes')) {
            Schema::table('Representantes', function (Blueprint $table) {
                $table->boolean('agente_fifa')->default(false);
                $table->boolean('oficial_cbf')->default(false);
                $table->boolean('sem_registro')->default(false);
            });

            try {
                DB::statement("ALTER TABLE Representantes ADD CONSTRAINT CHK_representantes_flags CHECK ((sem_registro = 0) OR (agente_fifa = 0 AND oficial_cbf = 0))");
            } catch (\Throwable $e) {}
        } elseif (Schema::hasTable('representantes')) {
            Schema::table('representantes', function (Blueprint $table) {
                $table->boolean('agente_fifa')->default(false);
                $table->boolean('oficial_cbf')->default(false);
                $table->boolean('sem_registro')->default(false);
            });
            try {
                DB::statement("ALTER TABLE representantes ADD CONSTRAINT CHK_representantes_flags CHECK ((sem_registro = 0) OR (agente_fifa = 0 AND oficial_cbf = 0))");
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        foreach (['Representantes','representantes'] as $tbl) {
            if (Schema::hasTable($tbl)) {
                try { DB::statement("ALTER TABLE {$tbl} DROP CONSTRAINT CHK_representantes_flags"); } catch (\Throwable $e) {}
                Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                    if (Schema::hasColumn($tbl, 'agente_fifa')) $table->dropColumn('agente_fifa');
                    if (Schema::hasColumn($tbl, 'oficial_cbf')) $table->dropColumn('oficial_cbf');
                    if (Schema::hasColumn($tbl, 'sem_registro')) $table->dropColumn('sem_registro');
                });
            }
        }
    }
};
