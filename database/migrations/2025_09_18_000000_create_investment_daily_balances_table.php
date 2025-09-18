<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('investment_daily_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->dateTime('snapshot_at'); // data/hora do snapshot (permite mais de um por dia)
            $table->decimal('total_amount', 20, 6); // soma dos últimos amounts dos ativos
            $table->timestamps();

            $table->index(['user_id','snapshot_at']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_daily_balances');
    }
};
