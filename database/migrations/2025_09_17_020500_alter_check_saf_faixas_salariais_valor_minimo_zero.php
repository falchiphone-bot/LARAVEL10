<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Relaxa o CHECK para permitir valor_minimo >= 0
        try {
            DB::statement("ALTER TABLE saf_faixas_salariais DROP CONSTRAINT CHK_faixa_valores");
        } catch (\Throwable $e) {}
        try {
            DB::statement("ALTER TABLE saf_faixas_salariais ADD CONSTRAINT CHK_faixa_valores CHECK (valor_minimo >= 0 AND valor_maximo >= valor_minimo)");
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        // Restaura a versão anterior (> 0)
        try {
            DB::statement("ALTER TABLE saf_faixas_salariais DROP CONSTRAINT CHK_faixa_valores");
        } catch (\Throwable $e) {}
        try {
            DB::statement("ALTER TABLE saf_faixas_salariais ADD CONSTRAINT CHK_faixa_valores CHECK (valor_minimo > 0 AND valor_maximo >= valor_minimo)");
        } catch (\Throwable $e) {}
    }
};
