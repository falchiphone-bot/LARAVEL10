//pedro-23-05-2023
Schema::create('feriados', function (Blueprint $table) {
    $table->id();
    $table->string('nome');
    $table->date('data');
    $table->timestamps();
});
