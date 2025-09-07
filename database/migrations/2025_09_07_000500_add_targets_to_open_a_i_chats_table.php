<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('open_a_i_chats', function (Blueprint $table) {
            if (!Schema::hasColumn('open_a_i_chats','target_min')) {
                $table->decimal('target_min', 12, 2)->nullable()->after('code');
            }
            if (!Schema::hasColumn('open_a_i_chats','target_avg')) {
                $table->decimal('target_avg', 12, 2)->nullable()->after('target_min');
            }
            if (!Schema::hasColumn('open_a_i_chats','target_max')) {
                $table->decimal('target_max', 12, 2)->nullable()->after('target_avg');
            }
            $table->index(['target_min']);
            $table->index(['target_avg']);
            $table->index(['target_max']);
        });
    }

    public function down(): void
    {
        Schema::table('open_a_i_chats', function (Blueprint $table) {
            if (Schema::hasColumn('open_a_i_chats','target_min')) {
                $table->dropIndex(['target_min']);
                $table->dropColumn('target_min');
            }
            if (Schema::hasColumn('open_a_i_chats','target_avg')) {
                $table->dropIndex(['target_avg']);
                $table->dropColumn('target_avg');
            }
            if (Schema::hasColumn('open_a_i_chats','target_max')) {
                $table->dropIndex(['target_max']);
                $table->dropColumn('target_max');
            }
        });
    }
};
