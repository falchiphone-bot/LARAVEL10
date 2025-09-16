<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('open_a_i_code_orders', function (Blueprint $table) {
            // Valor da última cotação capturada e quando foi capturada
            $table->decimal('quote_value', 18, 6)->nullable()->after('value');
            $table->dateTime('quote_updated_at')->nullable()->after('quote_value');
        });
    }

    public function down(): void
    {
        Schema::table('open_a_i_code_orders', function (Blueprint $table) {
            if (Schema::hasColumn('open_a_i_code_orders', 'quote_updated_at')) {
                $table->dropColumn('quote_updated_at');
            }
            if (Schema::hasColumn('open_a_i_code_orders', 'quote_value')) {
                $table->dropColumn('quote_value');
            }
        });
    }
};
