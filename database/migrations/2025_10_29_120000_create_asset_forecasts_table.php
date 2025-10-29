<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_forecasts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('symbol', 64); // símbolo em UPPERCASE
            // datetime2(7) em SQL Server: em Laravel, dateTime com precisão 7
            $table->dateTime('forecast_at', 7)->nullable();
            $table->timestamps();

            $table->unique(['user_id','symbol'], 'uq_asset_forecasts_user_symbol');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // Nota: não há FK para símbolo (string livre, parser do texto)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_forecasts');
    }
};
