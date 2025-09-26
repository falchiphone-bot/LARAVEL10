<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asset_daily_stats', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_daily_stats', 'close_value')) {
                $table->decimal('close_value', 18, 6)->nullable()->after('p95');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asset_daily_stats', function (Blueprint $table) {
            if (Schema::hasColumn('asset_daily_stats', 'close_value')) {
                $table->dropColumn('close_value');
            }
        });
    }
};
