<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saf_tipos_prestadores', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cidade')->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('pais')->nullable();
            $table->timestamps();
        });

        // Promover timestamps para datetime2(7) quando SQL Server
        try {
            $driver = DB::getDriverName();
        } catch (\Throwable $e) { $driver = null; }

        if ($driver === 'sqlsrv') {
            foreach (['created_at','updated_at'] as $col) {
                try { DB::statement("ALTER TABLE saf_tipos_prestadores ALTER COLUMN {$col} datetime2(7) NULL"); }
                catch (\Throwable $e) { try { DB::statement("ALTER TABLE saf_tipos_prestadores ALTER COLUMN {$col} datetime2 NULL"); } catch (\Throwable $e2) {} }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saf_tipos_prestadores');
    }
};
