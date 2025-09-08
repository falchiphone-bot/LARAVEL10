<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('open_a_i_code_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('chat_id')->nullable();
            $table->string('code', 50);
            $table->string('type', 10); // compra | venda
            $table->decimal('quantity', 18, 6);
            $table->timestamps();

            $table->index(['code']);
            $table->index(['chat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('open_a_i_code_orders');
    }
};
