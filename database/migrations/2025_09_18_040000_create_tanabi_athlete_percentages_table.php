<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tanabi_athlete_percentages', function (Blueprint $table) {
            $table->id();
            $table->string('athlete_name');
            $table->decimal('tanabi_percentage',8,4); // percentual TANABI SAF
            $table->decimal('other_club_percentage',8,4); // percentual outro clube
            $table->unsignedBigInteger('other_club_id')->nullable();
            $table->string('other_club_name')->nullable(); // fallback nome livre
            $table->foreign('other_club_id')->references('id')->on('saf_clubes')->nullOnDelete();
            $table->timestamps(); // criadas como DATETIME padrão, alteramos abaixo se sqlsrv
        });
        if (DB::getDriverName()==='sqlsrv') {
            DB::statement("ALTER TABLE tanabi_athlete_percentages ALTER COLUMN created_at DATETIME2(7) NOT NULL");
            DB::statement("ALTER TABLE tanabi_athlete_percentages ALTER COLUMN updated_at DATETIME2(7) NOT NULL");
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('tanabi_athlete_percentages');
    }
};
