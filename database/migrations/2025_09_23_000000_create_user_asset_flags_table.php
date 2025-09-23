<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_asset_flags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('code', 50); // código do ativo (ex.: AAPL)
            $table->boolean('no_buy')->default(false); // true = NÃO COMPRAR
            $table->timestamps();

            $table->unique(['user_id','code']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_asset_flags');
    }
};
