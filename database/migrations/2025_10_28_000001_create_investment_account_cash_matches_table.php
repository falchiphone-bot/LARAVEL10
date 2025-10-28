<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('investment_account_cash_matches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('buy_event_id');
            $table->unsignedBigInteger('sell_event_id');
            $table->decimal('qty', 24, 8); // quantidade alocada do lote de compra para a venda
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['buy_event_id']);
            $table->index(['sell_event_id']);
            $table->unique(['user_id','buy_event_id','sell_event_id'], 'u_user_buy_sell');

            // FKs (soft constraints — podem ser comentadas se o driver não suportar)
            // Em SQL Server, duas FKs com cascade para a MESMA tabela podem causar "multiple cascade paths".
            // Mantemos cascade apenas em users. Para os eventos, deixamos sem onDelete explícito (NO ACTION),
            // que evita múltiplos caminhos de cascade e é compatível com SQL Server.
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('buy_event_id')->references('id')->on('investment_account_cash_events');
            $table->foreign('sell_event_id')->references('id')->on('investment_account_cash_events');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_account_cash_matches');
    }
};
