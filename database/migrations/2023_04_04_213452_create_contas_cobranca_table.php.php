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
        Schema::create('ContasCobranca', function (Blueprint $table) {
            $table->id();
            $table->integer('EmpresaID');
            $table->string('conta');
            $table->string('agencia');
            $table->string('posto');
            $table->string('associadobeneficiario');
            $table->string('token_conta');
            $table->integer('idDevSicredi');
            $table->timestamps();
        });

        Schema::create('DevSicrediAPI', function (Blueprint $table) {
            $table->id();
            $table->string('DESENVOLVEDOR');
            $table->string('SICREDI_CLIENT_ID');
            $table->string('SICREDI_CLIENT_SECRET');
            $table->string('SICREDI_TOKEN');
            $table->string('URL_API');
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

