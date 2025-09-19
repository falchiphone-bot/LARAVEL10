<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('envio_arquivos', function (Blueprint $table) {
            $table->dateTime('last_transcode_at', 7)->nullable()->after('transcode_error');
        });
    }

    public function down(): void
    {
        Schema::table('envio_arquivos', function (Blueprint $table) {
            $table->dropColumn('last_transcode_at');
        });
    }
};
