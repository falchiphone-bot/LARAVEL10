<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('open_a_i_chats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('title', 200);
            // Para SQL Server, JSON é nvarchar(max)
            $table->text('messages');
            $table->dateTime('created_at', 0)->useCurrent();
            $table->dateTime('updated_at', 0)->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('open_a_i_chats');
    }
};
