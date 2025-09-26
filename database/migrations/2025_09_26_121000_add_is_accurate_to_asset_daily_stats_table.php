<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asset_daily_stats', function (Blueprint $table) {
            $table->boolean('is_accurate')->nullable()->after('close_value');
            $table->index('is_accurate');
        });
    }

    public function down(): void
    {
        Schema::table('asset_daily_stats', function (Blueprint $table) {
            $table->dropIndex(['is_accurate']);
            $table->dropColumn('is_accurate');
        });
    }
};
