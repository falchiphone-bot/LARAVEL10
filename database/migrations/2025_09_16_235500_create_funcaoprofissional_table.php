<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('FuncaoProfissional')) {
            Schema::create('FuncaoProfissional', function (Blueprint $table) {
                $table->id();
                $table->string('nome');
                $table->timestamps();
            });
        }

        // Promover timestamps para datetime2(7) quando SQL Server
        try { $driver = DB::getDriverName(); } catch (\Throwable $e) { $driver = null; }
        if ($driver === 'sqlsrv' && Schema::hasTable('FuncaoProfissional')) {
            foreach (['created_at','updated_at'] as $col) {
                try { DB::statement("ALTER TABLE FuncaoProfissional ALTER COLUMN {$col} datetime2(7) NULL"); }
                catch (\Throwable $e) { try { DB::statement("ALTER TABLE FuncaoProfissional ALTER COLUMN {$col} datetime2 NULL"); } catch (\Throwable $e2) {} }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('FuncaoProfissional');
    }
};
