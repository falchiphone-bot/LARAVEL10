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
        Schema::create('faturamento', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('EmpresaID');
            $table->date('data');
            $table->decimal('ValorFaturamento',18,2);
            $table->decimal('PercentualImposto',18,2);
            $table->decimal('ValorImposto',18,2);
            $table->decimal('ValorBaseLucroLiquido',18,2);
            $table->decimal('PercentualLucroLiquido',18,2);
            $table->decimal('LucroLiquido',18,2);
            $table->decimal('LancadoPor',18,2);
            $table->timestamps();
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

