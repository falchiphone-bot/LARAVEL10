<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajusta precisão dos timestamps para datetime2(7) nas tabelas criadas agora
        Schema::table('envios', function (Blueprint $table) {
            $table->dateTimeTz('created_at', 7)->change();
            $table->dateTimeTz('updated_at', 7)->change();
        });
        Schema::table('envio_arquivos', function (Blueprint $table) {
            $table->dateTimeTz('created_at', 7)->change();
            $table->dateTimeTz('updated_at', 7)->change();
        });
    }

    public function down(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            $table->dateTimeTz('created_at')->change();
            $table->dateTimeTz('updated_at')->change();
        });
        Schema::table('envio_arquivos', function (Blueprint $table) {
            $table->dateTimeTz('created_at')->change();
            $table->dateTimeTz('updated_at')->change();
        });
    }
};
