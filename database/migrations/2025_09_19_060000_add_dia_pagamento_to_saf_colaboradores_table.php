<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saf_colaboradores', function (Blueprint $table) {
            if (!Schema::hasColumn('saf_colaboradores', 'dia_pagamento')) {
                $table->unsignedTinyInteger('dia_pagamento')->nullable()->after('valor_salario');
                $table->index('dia_pagamento');
            }
        });
    }

    public function down(): void
    {
        Schema::table('saf_colaboradores', function (Blueprint $table) {
            if (Schema::hasColumn('saf_colaboradores', 'dia_pagamento')) {
                $table->dropIndex(['dia_pagamento']);
                $table->dropColumn('dia_pagamento');
            }
        });
    }
};
