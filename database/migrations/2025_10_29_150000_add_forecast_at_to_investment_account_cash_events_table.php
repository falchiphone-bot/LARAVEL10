<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('investment_account_cash_events', function (Blueprint $table) {
            // SQL Server: use datetime2(7) via precision parameter on dateTime
            $table->dateTime('forecast_at', 7)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investment_account_cash_events', function (Blueprint $table) {
            $table->dropColumn('forecast_at');
        });
    }
};
