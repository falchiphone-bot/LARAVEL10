<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envio_arquivo_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('envio_arquivo_id')->constrained('envio_arquivos')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->dateTime('expires_at', 7)->nullable();
            $table->boolean('allow_download')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps(7);
            $table->index(['envio_arquivo_id','expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envio_arquivo_tokens');
    }
};
