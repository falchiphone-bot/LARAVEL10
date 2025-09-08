<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('investment_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('total_invested', 15, 2);
            $table->string('account_name', 100);
            $table->string('broker', 100);
            $table->timestamps();
            $table->index(['user_id','date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_accounts');
    }
};
