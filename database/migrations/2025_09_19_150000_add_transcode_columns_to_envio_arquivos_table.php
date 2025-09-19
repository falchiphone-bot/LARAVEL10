<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('envio_arquivos', function (Blueprint $table) {
            $table->string('mp4_path')->nullable()->after('path');
            $table->string('hls_path')->nullable()->after('mp4_path');
            $table->string('transcode_status', 32)->nullable()->after('mime_type'); // pending|processing|done|failed
            $table->text('transcode_error')->nullable()->after('transcode_status');
        });
    }

    public function down(): void
    {
        Schema::table('envio_arquivos', function (Blueprint $table) {
            $table->dropColumn(['mp4_path','hls_path','transcode_status','transcode_error']);
        });
    }
};
