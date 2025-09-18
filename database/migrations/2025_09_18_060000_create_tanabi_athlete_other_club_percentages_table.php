<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tanabi_athlete_other_club_percentages', function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('tanabi_athlete_percentage_id'); // referência ao registro principal (que guarda % TANABI e total base)
            $table->unsignedBigInteger('other_club_id')->nullable(); // FK saf_clubes
            $table->string('other_club_name')->nullable(); // fallback texto
            $table->decimal('percentage',8,4); // percentual referente a ESTE outro clube
            $table->timestamps();
            $table->foreign('tanabi_athlete_percentage_id','fk_other_pct_main')
                ->references('id')->on('tanabi_athlete_percentages')->onDelete('cascade');
            $table->foreign('other_club_id','fk_other_pct_club')
                ->references('id')->on('saf_clubes')->nullOnDelete();
        });
        if (DB::getDriverName()==='sqlsrv') {
            DB::statement("ALTER TABLE tanabi_athlete_other_club_percentages ALTER COLUMN created_at DATETIME2(7) NOT NULL");
            DB::statement("ALTER TABLE tanabi_athlete_other_club_percentages ALTER COLUMN updated_at DATETIME2(7) NOT NULL");
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('tanabi_athlete_other_club_percentages');
    }
};
