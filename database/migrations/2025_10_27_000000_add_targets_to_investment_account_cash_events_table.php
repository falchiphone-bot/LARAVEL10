<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('investment_account_cash_events', function (Blueprint $table) {
            // Meta de valor a ser atingido (mesma precisão do amount)
            $table->decimal('target_amount', 20, 6)->nullable()->after('amount');
            // Probabilidade associada em percentual (0..100)
            $table->decimal('target_probability_pct', 5, 2)->nullable()->after('target_amount');
            $table->index(['user_id','target_probability_pct'], 'iac_events_user_prob_idx');
        });
    }

    public function down(): void
    {
        Schema::table('investment_account_cash_events', function (Blueprint $table) {
            if (Schema::hasColumn('investment_account_cash_events', 'target_probability_pct')) {
                $table->dropIndex('iac_events_user_prob_idx');
                $table->dropColumn('target_probability_pct');
            }
            if (Schema::hasColumn('investment_account_cash_events', 'target_amount')) {
                $table->dropColumn('target_amount');
            }
        });
    }
};
