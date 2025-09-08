<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('open_a_i_code_orders', function (Blueprint $table) {
            $table->decimal('value', 18, 6)->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('open_a_i_code_orders', function (Blueprint $table) {
            $table->dropColumn('value');
        });
    }
};
