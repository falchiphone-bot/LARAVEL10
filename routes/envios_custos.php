// Rota para editar custo de um envio
Route::get('Envios/{Envio}/custos/{custo}/edit', [\App\Http\Controllers\EnvioCustoController::class, 'edit'])
    ->name('Envios.custos.edit');
