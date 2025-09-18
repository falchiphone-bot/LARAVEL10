<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tanabi_athlete_percentages') && !Schema::hasColumn('tanabi_athlete_percentages','formando_base_id')) {
            Schema::table('tanabi_athlete_percentages', function(Blueprint $table){
                $table->unsignedBigInteger('formando_base_id')->nullable()->after('id');
                $table->foreign('formando_base_id')->references('id')->on('formandobase')->nullOnDelete();
                $table->index('formando_base_id','idx_tap_formando_base_id');
            });
            // Opcional: tentar vincular registros existentes por nome exato
            try {
                $rows = DB::table('tanabi_athlete_percentages')->whereNull('formando_base_id')->get();
                foreach ($rows as $r) {
                    $fb = DB::table('formandobase')->whereRaw('LOWER(nome) = ?', [mb_strtolower($r->athlete_name)])->first();
                    if ($fb) {
                        DB::table('tanabi_athlete_percentages')->where('id',$r->id)->update(['formando_base_id'=>$fb->id]);
                    }
                }
            } catch (Throwable $e) {
                // silenciosamente ignorar se der problema; log poderia ser adicionado
            }
        }
    }
    public function down(): void
    {
        if (Schema::hasTable('tanabi_athlete_percentages') && Schema::hasColumn('tanabi_athlete_percentages','formando_base_id')) {
            Schema::table('tanabi_athlete_percentages', function(Blueprint $table){
                $table->dropForeign(['formando_base_id']);
                $table->dropIndex('idx_tap_formando_base_id');
                $table->dropColumn('formando_base_id');
            });
        }
    }
};
