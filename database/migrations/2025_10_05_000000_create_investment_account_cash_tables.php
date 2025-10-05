<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Snapshots de caixa (saldo disponível)
        Schema::create('investment_account_cash_snapshots', function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('account_id');
            $table->dateTime('snapshot_at');
            $table->decimal('available_amount',20,6)->default(0);
            $table->decimal('future_amount',20,6)->nullable();
            $table->date('future_date')->nullable();
            $table->string('raw_hash',64)->nullable();
            $table->timestamps();
            $table->index(['user_id','account_id','snapshot_at']);
            $table->unique(['raw_hash']);
        });
        // Eventos de caixa (dividendos, impostos, depósitos, retiradas...)
        Schema::create('investment_account_cash_events', function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('account_id');
            $table->date('event_date');
            $table->date('settlement_date')->nullable();
            $table->string('category',30); // dividend, tax, add, withdraw, other
            $table->string('title',120);
            $table->string('detail',255)->nullable();
            $table->decimal('amount',20,6); // positivo entradas, negativo saídas
            $table->string('currency',10)->default('USD');
            $table->string('status',40)->nullable();
            $table->string('source',40)->default('avenue_screen_cash');
            $table->string('group_hash',64)->nullable();
            $table->timestamps();
            $table->index(['user_id','account_id','event_date']);
            $table->unique(['group_hash']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('investment_account_cash_events');
        Schema::dropIfExists('investment_account_cash_snapshots');
    }
};
