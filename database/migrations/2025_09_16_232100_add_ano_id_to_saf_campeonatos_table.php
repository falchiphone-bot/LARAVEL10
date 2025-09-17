<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saf_campeonatos', function (Blueprint $table) {
            $table->foreignId('ano_id')->nullable()->constrained('saf_anos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('saf_campeonatos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ano_id');
        });
    }
};
