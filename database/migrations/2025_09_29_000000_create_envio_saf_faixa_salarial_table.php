<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('envio_saf_faixa_salarial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('envio_id');
            $table->unsignedBigInteger('saf_faixa_salarial_id');
            $table->timestamps();

            $table->foreign('envio_id')->references('id')->on('envios')->onDelete('cascade');
            $table->foreign('saf_faixa_salarial_id')->references('id')->on('saf_faixas_salariais')->onDelete('cascade');
            $table->unique(['envio_id', 'saf_faixa_salarial_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('envio_saf_faixa_salarial');
    }
};
