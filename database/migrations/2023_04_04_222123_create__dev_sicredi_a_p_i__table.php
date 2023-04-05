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
        Schema::dropIfExists('_dev_sicredi_a_p_i_');
    }
};
