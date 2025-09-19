<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saf_colaboradores', function (Blueprint $table) {
            if (!Schema::hasColumn('saf_colaboradores', 'forma_pagamento_nome')) {
                $table->string('forma_pagamento_nome')->nullable()->after('pix_nome');
            }
            $table->foreign('forma_pagamento_nome')->references('nome')->on('forma_pagamentos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('saf_colaboradores', function (Blueprint $table) {
            $table->dropForeign(['forma_pagamento_nome']);
            $table->dropColumn('forma_pagamento_nome');
        });
    }
};
