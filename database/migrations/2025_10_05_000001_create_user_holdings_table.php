<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_holdings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('code', 32);
            $table->decimal('quantity', 18, 6)->default(0);
            $table->decimal('avg_price', 18, 6)->default(0); // preço médio
            $table->decimal('invested_value', 18, 2)->default(0); // valor investido acumulado
            $table->decimal('current_price', 18, 6)->nullable(); // última cotação conhecida
            $table->string('currency', 8)->nullable();
            $table->timestamps();
            $table->unique(['user_id','code','account_id']);
            $table->index(['user_id','code']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('user_holdings');
    }
};
