<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envio_arquivo_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envio_arquivo_id')->constrained('envio_arquivos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps(7);
            $table->unique(['envio_arquivo_id','user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envio_arquivo_shares');
    }
};
