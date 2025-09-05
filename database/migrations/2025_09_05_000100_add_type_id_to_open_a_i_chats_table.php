<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('open_a_i_chats', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->nullable()->after('title');
            $table->index('type_id');
            $table->foreign('type_id')->references('id')->on('openai_chat_types')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('open_a_i_chats', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->dropIndex(['type_id']);
            $table->dropColumn('type_id');
        });
    }
};
