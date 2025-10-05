<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if(Schema::hasTable('user_holdings') && !Schema::hasColumn('user_holdings','deleted_at')){
            Schema::table('user_holdings', function(Blueprint $table){
                $table->softDeletes();
            });
        }
    }
    public function down(): void
    {
        if(Schema::hasTable('user_holdings') && Schema::hasColumn('user_holdings','deleted_at')){
            Schema::table('user_holdings', function(Blueprint $table){
                $table->dropSoftDeletes();
            });
        }
    }
};
