<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('openai_chat_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('user_id');
            $table->string('original_name');
            $table->string('path'); // storage path relative to disk
            $table->string('disk')->default('public');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->integer('message_index')->nullable();

            // SQL Server-friendly timestamps
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent();

            $table->index('chat_id');
            $table->index('user_id');
            $table->foreign('chat_id')->references('id')->on('open_a_i_chats')->onDelete('cascade');
            // Em SQL Server, evitar múltiplos caminhos em cascata: não fazer cascade pelo usuário
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('openai_chat_attachments');
    }
};
