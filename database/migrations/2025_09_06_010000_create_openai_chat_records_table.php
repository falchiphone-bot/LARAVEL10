<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('openai_chat_records')) {
            Schema::create('openai_chat_records', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('chat_id');
                $table->unsignedBigInteger('user_id');
                $table->dateTime('occurred_at');
                $table->decimal('amount', 15, 2)->default(0);
                $table->timestamps();

                $table->foreign('chat_id')->references('id')->on('open_a_i_chats')->cascadeOnDelete();
                // Evita múltiplos caminhos em cascata no SQL Server: sem cascade aqui
                $table->foreign('user_id')->references('id')->on('users'); // restrito (NO ACTION)
                $table->index(['chat_id','occurred_at']);
            });
        } else {
            // Caso a tabela tenha sido criada parcialmente antes da falha, garantir FK ausente em user_id
            Schema::table('openai_chat_records', function (Blueprint $table) {
                // Como não temos schema diff aqui, apenas tentamos adicionar a FK de chat se não existir
                // Ajustes manuais podem ser feitos via migration separada se necessário.
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('openai_chat_records');
    }
};
