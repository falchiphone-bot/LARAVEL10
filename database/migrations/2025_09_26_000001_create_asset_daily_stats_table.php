<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 32)->index();
            $table->date('date')->index();
            $table->decimal('mean', 18, 6)->nullable();
            $table->decimal('median', 18, 6)->nullable();
            $table->decimal('p5', 18, 6)->nullable();
            $table->decimal('p95', 18, 6)->nullable();
            $table->timestamps();
            $table->unique(['symbol','date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_daily_stats');
    }
};
