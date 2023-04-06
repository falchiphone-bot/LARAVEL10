<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
            Schema::table('ContasCobranca', function (Blueprint $table) {
                $table->decimal('ValorTarifaCobranca')->nullable();
                $table->integer('Credito_Cobranca')->nullable();
                $table->integer('Tarifa_Cobranca')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
