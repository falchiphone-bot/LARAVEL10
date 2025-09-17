<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saf_federacoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cidade')->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('pais')->nullable();
            $table->timestamps();
        });

        // Em SQL Server, promover timestamps para datetime2(7)
        if (DB::getDriverName() === 'sqlsrv') {
            foreach (['created_at', 'updated_at'] as $col) {
                try {
                    DB::statement("ALTER TABLE saf_federacoes ALTER COLUMN {$col} datetime2(7) NULL");
                } catch (\Throwable $e) {
                    try { DB::statement("ALTER TABLE saf_federacoes ALTER COLUMN {$col} datetime2 NULL"); } catch (\Throwable $e2) {}
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saf_federacoes');
    }
};
