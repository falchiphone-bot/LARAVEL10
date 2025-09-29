<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('envio_custos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('envio_id')->constrained('envios')->onDelete('cascade');
            $table->string('nome',150);
            $table->decimal('valor',15,2)->default(0);
            $table->date('data');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['envio_id','data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envio_custos');
    }
};
