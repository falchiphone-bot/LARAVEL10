<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tanabi_athlete_percentages') && !Schema::hasColumn('tanabi_athlete_percentages','other_club_id')) {
            Schema::table('tanabi_athlete_percentages', function(Blueprint $table){
                $table->unsignedBigInteger('other_club_id')->nullable()->after('other_club_percentage');
                $table->foreign('other_club_id')->references('id')->on('saf_clubes')->nullOnDelete();
            });
        }
    }
    public function down(): void
    {
        if (Schema::hasTable('tanabi_athlete_percentages') && Schema::hasColumn('tanabi_athlete_percentages','other_club_id')) {
            Schema::table('tanabi_athlete_percentages', function(Blueprint $table){
                $table->dropForeign(['other_club_id']);
                $table->dropColumn('other_club_id');
            });
        }
    }
};
