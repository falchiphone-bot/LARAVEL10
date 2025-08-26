<?php

use App\Http\Controllers\ArquivosPublicos;
use App\Http\Controllers\Irmaos_Emaus_FichaControleArquivoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\LancamentosController;
use App\Http\Controllers\OpenAIController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\MailController;
use App\Http\Services;
use App\Http\Controllers\PacpieController;
use App\Http\Controllers\OrigemPacpieController;
use App\Http\Controllers\PDFController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// OpenAI Transcribe (sincrono/assíncrono)
Route::match(['get','post'], '/openai/transcribe', [OpenAIController::class, 'transcribe'])->name('openai.transcribe');
Route::get('/openai/transcribe/status/{jobId}', [OpenAIController::class, 'transcribeStatus'])->name('openai.transcribe.status');


Route::get('/vec-galeria/escritorio-2025', function () {
    $arquivos = Storage::allFiles('public/arquivos/vec-galeria/escritorio-2025');

    // Agrupa arquivos pela subpasta (relativa ao path base)
    $grupos = collect($arquivos)->groupBy(function ($caminho) {
        return Str::after(dirname($caminho), 'public/arquivos/vec-galeria/escritorio-2025');
    });

    // Transforma os caminhos em URLs públicas
    $galerias = $grupos->map(function ($arquivos) {
        return collect($arquivos)->map(function ($file) {
            return asset('storage/' . Str::after($file, 'public/'));
        });
    });

    return view('vec.vec-galeria-escritorio-2025', compact('galerias'));
});


Route::get('/vec-galeria', function () {
    $arquivos = Storage::allFiles('public/arquivos/vec-galeria');

    // Agrupa arquivos pela subpasta (relativa ao path base)
    $grupos = collect($arquivos)->groupBy(function ($caminho) {
        return Str::after(dirname($caminho), 'public/arquivos/vec-galeria');
    });

    // Transforma os caminhos em URLs públicas
    $galerias = $grupos->map(function ($arquivos) {
        return collect($arquivos)->map(function ($file) {
            return asset('storage/' . Str::after($file, 'public/'));
        });
    });

    return view('vec.vec-galeria', compact('galerias'));
});

Route::get('/vec-galeria-04-2025', function () {
    $mes = '04/2025';
    $arquivos = Storage::allFiles('public/arquivos/vec-galeria/2025/04-2025');

    // Agrupa arquivos pela subpasta (relativa ao path base)
    $grupos = collect($arquivos)->groupBy(function ($caminho) {
        return Str::after(dirname($caminho), 'public/arquivos/vec-galeria/2025/04-2025');
    });

    // Transforma os caminhos em URLs públicas
    $galerias = $grupos->map(function ($arquivos) {
        return collect($arquivos)->map(function ($file) {
            return asset('storage/' . Str::after($file, 'public/'));
        });
    });

    return view('vec.vec-galeria', compact('galerias', 'mes'));
});


Route::get('php', function () { return phpinfo();})->name('php');
Route::resource('teste', App\Http\Controllers\TesteController::class);



// aqui é a rota que vai chamar o método que vai fazer a autenticação ou página solicitada na chamada via url
// Route::get('/', function () {
//     return redirect('/dashboard');
// });

Route::get('/', function () {
    $dominio = request()->getHost(); // Obtém o domínio atual

    // dd($dominio);

    if ($dominio === 'tanabisaf.com.br') {
        return view('tanabisaf.index');
    } elseif ($dominio === 'vec.org.br') {
        return view('vec.index');
    }

    return redirect('/dashboard'); // Caso não seja nenhum dos domínios específicos
});


Route::get('sites', [App\Http\Controllers\LancamentosDocumentosController::class, 'sites'])->name('lancamentosdocumentos.sites');
Route::get('documentosvideos', [App\Http\Controllers\LancamentosDocumentosController::class, 'documentosvideos'])->name('lancamentosdocumentos.documentosvideos');
Route::get('alvaresflorence', [App\Http\Controllers\LancamentosDocumentosController::class, 'alvaresflorence'])->name('lancamentosdocumentos.alvaresflorence');
Route::get('/storage/arquivospublicos/{filename}', function ($filename) {
    // Coloque aqui a lógica para lidar com a requisição, como o envio do arquivo ou redirecionamento para ele.
    // Você pode usar a função `response()->file()` para enviar o arquivo.
    return response()->file('../storage/app/arquivos/' . $filename);
})->where('filename', '.*');

Route::get('/dashboard', function () {
    return view('dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

    ///// vec.org.çbr
    Route::get('vec.historia1', function ()
    { return view('vec.historia1');})->name('vec.historia1');
    Route::get('vec.contato', function ()
    { return view('vec.contato');})->name('vec.contato');
    Route::get('vec.categoria', function ()
    { return view('vec.categoria');})->name('vec.categoria');
    Route::get('vec.localtreino', function ()
    { return view('vec.localtreino');})->name('vec.localtreino');
    Route::get('vec.dadosbancarios', function ()
    { return view('vec.dadosbancarios');})->name('vec.dadosbancarios');
    Route::get('vec.comoparticipar', function ()
    { return view('vec.comoparticipar');})->name('vec.comoparticipar');
    Route::get('vec.transparencias', function ()
    { return view('vec.transparencias');})->name('vec.transparencias');
    Route::get('vec.certidoes', function ()
    { return view('vec.certidoes');})->name('vec.certidoes');

    //rota para baixar arquivo controlados usando uma controller
    Route::get('download/{id_arquivo}', ArquivosPublicos::class)->name('download');

   ///// tanabisaf.com.br CATEGORIAS
   Route::get('tanabisaf.historia1', function ()
    { return view('tanabisaf.historia1');})->name('tanabisaf.historia1');
   Route::get('tanabisaf.transparencias', function ()
   { return view('tanabisaf.transparencias');})->name('tanabisaf.transparencias');
   Route::get('tanabisaf.contato', function ()
   { return view('tanabisaf.contato');})->name('tanabisaf.contato');
   Route::get('tanabisaf.categoria', function ()
    { return view('tanabisaf.categoria');})->name('tanabisaf.categoria');


// SUB-11
Route::get('tanabisaf.categoriasub11', function ()
    { return view('tanabisaf.jogos.2025.sub11.categoriasub11');})
    ->name('tanabisaf.categoriasub11');
    Route::get('tanabisaf.jogos.2025.sub11.rodada01', function ()
    { return view('tanabisaf.jogos.2025.sub11.rodada01-01062025');})
    ->name('tanabisaf.jogos.2025.sub11.rodada01');
 Route::get('tanabisaf.jogos.2025.sub11.rodada02', function ()
    { return view('tanabisaf.jogos.2025.sub11.rodada02-08062025');})
    ->name('tanabisaf.jogos.2025.sub11.rodada02');

// SUB-12
Route::get('tanabisaf.categoriasub12', function ()
    { return view('tanabisaf.jogos.2025.sub12.categoriasub12');})
    ->name('tanabisaf.categoriasub12');
    Route::get('tanabisaf.jogos.2025.sub12.rodada01', function ()
    { return view('tanabisaf.jogos.2025.sub12.rodada01-01062025');})
    ->name('tanabisaf.jogos.2025.sub12.rodada01');
   Route::get('tanabisaf.jogos.2025.sub12.rodada02', function ()
    { return view('tanabisaf.jogos.2025.sub12.rodada02-08062025');})
    ->name('tanabisaf.jogos.2025.sub12.rodada02');




// SUB-13
Route::get('tanabisaf.categoriasub13', function ()
    { return view('tanabisaf.jogos.2025.sub13.categoriasub13');})->name('tanabisaf.categoriasub13');
    Route::get('tanabisaf.jogos.2025.sub13.rodada01', function ()
    { return view('tanabisaf.jogos.2025.sub13.rodada01-18052025');})->name('tanabisaf.jogos.2025.sub13.rodada01');
 Route::get('tanabisaf.jogos.2025.sub13.rodada02', function ()
    { return view('tanabisaf.jogos.2025.sub13.rodada02-25052025');})->name('tanabisaf.jogos.2025.sub13.rodada02');
Route::get('tanabisaf.jogos.2025.sub13.rodada03', function ()
    { return view('tanabisaf.jogos.2025.sub13.rodada03-01062025');})
    ->name('tanabisaf.jogos.2025.sub13.rodada03');
Route::get('tanabisaf.jogos.2025.sub13.rodada04', function ()
    { return view('tanabisaf.jogos.2025.sub13.rodada04-08062025');})
    ->name('tanabisaf.jogos.2025.sub13.rodada04');




// SUB-14
    Route::get('tanabisaf.categoriasub14', function ()
    { return view('tanabisaf.jogos.2025.sub14.categoriasub14');})->name('tanabisaf.categoriasub14');
    Route::get('tanabisaf.jogos.2025.sub14.rodada01', function ()
    { return view('tanabisaf.jogos.2025.sub14.rodada01-18052025');})->name('tanabisaf.jogos.2025.sub14.rodada01');
 Route::get('tanabisaf.jogos.2025.sub14.rodada02', function ()
    { return view('tanabisaf.jogos.2025.sub14.rodada02-25052025');})->name('tanabisaf.jogos.2025.sub14.rodada02');
Route::get('tanabisaf.jogos.2025.sub14.rodada03', function ()
    { return view('tanabisaf.jogos.2025.sub14.rodada03-01062025');})
    ->name('tanabisaf.jogos.2025.sub14.rodada03');
Route::get('tanabisaf.jogos.2025.sub14.rodada04', function ()
    { return view('tanabisaf.jogos.2025.sub14.rodada04-08062025');})
    ->name('tanabisaf.jogos.2025.sub14.rodada04');




// SUB-15
Route::get('tanabisaf.categoriasub15', function ()
    { return view('tanabisaf.jogos.categoriasub15');})->name('tanabisaf.categoriasub15');
    Route::get('tanabisaf.jogos.2025.sub15.categoriasub15', function ()
    { return view('tanabisaf.jogos.2025.sub15.categoriasub15');})->name('tanabisaf.jogos.2025.sub15.categoriasub15');
    Route::get('tanabisaf.jogos.2025.sub15.rodada01', function ()
    { return view('tanabisaf.jogos.2025.sub15.rodada01-12042025');})->name('tanabisaf.jogos.2025.sub15.rodada01');
    Route::get('tanabisaf.jogos.2025.sub15.rodada02', function ()
    { return view('tanabisaf.jogos.2025.sub15.rodada02-19042025');})->name('tanabisaf.jogos.2025.sub15.rodada02');
    Route::get('tanabisaf.jogos.2025.sub15.rodada03', function ()
    { return view('tanabisaf.jogos.2025.sub15.rodada03-26042025');})->name('tanabisaf.jogos.2025.sub15.rodada03');
    Route::get('tanabisaf.jogos.2025.sub15.rodada04', function ()
    { return view('tanabisaf.jogos.2025.sub15.rodada04-10052025');})->name('tanabisaf.jogos.2025.sub15.rodada04');
  Route::get('tanabisaf.jogos.2025.sub15.rodada05', function ()
    { return view('tanabisaf.jogos.2025.sub15.rodada05-17052025');})->name('tanabisaf.jogos.2025.sub15.rodada05');
    Route::get('tanabisaf.jogos.2025.sub15.rodada06', function ()
    { return view('tanabisaf.jogos.2025.sub15.rodada06-24052025');})->name('tanabisaf.jogos.2025.sub15.rodada06');
  Route::get('tanabisaf.jogos.2025.sub15.rodada07', function ()
    { return view('tanabisaf.jogos.2025.sub15.rodada07-31052025');})
    ->name('tanabisaf.jogos.2025.sub15.rodada07');
Route::get('tanabisaf.jogos.2025.sub15.rodada08', function ()
    { return view('tanabisaf.jogos.2025.sub15.rodada08-07062025');})
    ->name('tanabisaf.jogos.2025.sub15.rodada08');

    Route::get('tanabisaf.jogos.2025.sub15.rodada09', function ()
    { return view('tanabisaf.jogos.2025.sub15.rodada09-14062025');})
    ->name('tanabisaf.jogos.2025.sub15.rodada09');

// SUB-17
Route::get('tanabisaf.categoriasub17', function ()
    { return view('tanabisaf.jogos.categoriasub17');})->name('tanabisaf.categoriasub17');
    Route::get('tanabisaf.jogos.2025.sub17.categoriasub17', function ()
    { return view('tanabisaf.jogos.2025.sub17.categoriasub17');})->name('tanabisaf.jogos.2025.sub17.categoriasub17');
    Route::get('tanabisaf.jogos.2025.sub17.rodada01', function ()
    { return view('tanabisaf.jogos.2025.sub17.rodada01-12042025');})->name('tanabisaf.jogos.2025.sub17.rodada01');
    Route::get('tanabisaf.jogos.2025.sub17.rodada02', function ()
    { return view('tanabisaf.jogos.2025.sub17.rodada02-19042025');})->name('tanabisaf.jogos.2025.sub17.rodada02');
    Route::get('tanabisaf.jogos.2025.sub17.rodada03', function ()
    { return view('tanabisaf.jogos.2025.sub17.rodada03-26042025');})->name('tanabisaf.jogos.2025.sub17.rodada03');
    Route::get('tanabisaf.jogos.2025.sub17.rodada04', function ()
    { return view('tanabisaf.jogos.2025.sub17.rodada04-10052025');})->name('tanabisaf.jogos.2025.sub17.rodada04');
  Route::get('tanabisaf.jogos.2025.sub17.rodada05', function ()
    { return view('tanabisaf.jogos.2025.sub17.rodada05-17052025');})->name('tanabisaf.jogos.2025.sub17.rodada05');
    Route::get('tanabisaf.jogos.2025.sub17.rodada06', function ()
    { return view('tanabisaf.jogos.2025.sub17.rodada06-24052025');})->name('tanabisaf.jogos.2025.sub17.rodada06');
 Route::get('tanabisaf.jogos.2025.sub17.rodada07', function ()
    { return view('tanabisaf.jogos.2025.sub17.rodada07-31052025');})
    ->name('tanabisaf.jogos.2025.sub17.rodada07');
Route::get('tanabisaf.jogos.2025.sub17.rodada08', function ()
    { return view('tanabisaf.jogos.2025.sub17.rodada08-07062025');})
    ->name('tanabisaf.jogos.2025.sub17.rodada08');
Route::get('tanabisaf.jogos.2025.sub17.rodada09', function ()
    { return view('tanabisaf.jogos.2025.sub17.rodada09-14062025');})
    ->name('tanabisaf.jogos.2025.sub17.rodada09');


    Route::get('tanabisaf.tanabiformando', function ()
    { return view('tanabisaf.tanabiformando.index');})->name('tanabisaf.tanabiformando');
    Route::get('tanabisaf.tanabiformando.alvaresflorence', function ()
    { return view('tanabisaf.tanabiformando.alvaresflorence.index');})->name('tanabisaf.tanabiformando.alvaresflorence');



    Route::get('tanabisaf.categoriasub20', function ()
    { return view('tanabisaf.jogos.categoriasub20');})->name('tanabisaf.categoriasub20');
    Route::get('tanabisaf.jogos.2025.sub20.categoriasub20', function ()
    { return view('tanabisaf.jogos.2025.sub20.categoriasub20');})->name('tanabisaf.jogos.2025.sub20.categoriasub20');
    Route::get('tanabisaf.jogos.2025.sub20.rodada01', function ()
    { return view('tanabisaf.jogos.2025.sub20.rodada01-25042025');})->name('tanabisaf.jogos.2025.sub20.rodada01');
    Route::get('tanabisaf.jogos.2025.sub20.rodada02', function ()
    { return view('tanabisaf.jogos.2025.sub20.rodada02-02052025');})->name('tanabisaf.jogos.2025.sub20.rodada02');
    Route::get('tanabisaf.jogos.2025.sub20.rodada03', function ()
    { return view('tanabisaf.jogos.2025.sub20.rodada03-10052025');})->name('tanabisaf.jogos.2025.sub20.rodada03');
 Route::get('tanabisaf.jogos.2025.sub20.rodada04', function ()
    { return view('tanabisaf.jogos.2025.sub20.rodada04-18052025');})->name('tanabisaf.jogos.2025.sub20.rodada04');
Route::get('tanabisaf.jogos.2025.sub20.rodada05', function ()
    { return view('tanabisaf.jogos.2025.sub20.rodada05-23052025');})->name('tanabisaf.jogos.2025.sub20.rodada05');
Route::get('tanabisaf.jogos.2025.sub20.rodada06', function ()
    { return view('tanabisaf.jogos.2025.sub20.rodada06-30052025');})
    ->name('tanabisaf.jogos.2025.sub20.rodada06');
Route::get('tanabisaf.jogos.2025.sub20.rodada07', function ()
    { return view('tanabisaf.jogos.2025.sub20.rodada07-07062025');})
    ->name('tanabisaf.jogos.2025.sub20.rodada07');
    Route::get('tanabisaf.jogos.2025.sub20.rodada08', function ()
    { return view('tanabisaf.jogos.2025.sub20.rodada08-11062025');})
    ->name('tanabisaf.jogos.2025.sub20.rodada08');



    Route::get('tanabisaf.jogos.2025.sub20.rodada11', function ()
    { return view('tanabisaf.jogos.2025.sub20.rodada11-11072025');})
    ->name('tanabisaf.jogos.2025.sub20.rodada11');
    Route::get('tanabisaf.jogos.2025.sub20.rodada12', function ()
    { return view('tanabisaf.jogos.2025.sub20.rodada12-18072025');})
    ->name('tanabisaf.jogos.2025.sub20.rodada12');
    Route::get('tanabisaf.jogos.2025.sub20.rodada13', function ()
    { return view('tanabisaf.jogos.2025.sub20.rodada13-25072025');})
    ->name('tanabisaf.jogos.2025.sub20.rodada13');



    Route::get('tanabisaf.categoriasub23', function ()
    { return view('tanabisaf.jogos.categoriasub23');})->name('tanabisaf.categoriasub23');
    Route::get('tanabisaf.jogos.2025.sub23.categoriasub23', function ()
    { return view('tanabisaf.jogos.2025.sub23.categoriasub23');})->name('tanabisaf.jogos.2025.sub23.categoriasub23');
     Route::get('tanabisaf.jogos.2025.sub23.rodada01', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada01-19042025');})->name('tanabisaf.jogos.2025.sub23.rodada01');
    Route::get('tanabisaf.jogos.2025.sub23.rodada02', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada02-25042025');})->name('tanabisaf.jogos.2025.sub23.rodada02');
    Route::get('tanabisaf.jogos.2025.sub23.rodada04', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada04-10052025');})->name('tanabisaf.jogos.2025.sub23.rodada04');
    Route::get('tanabisaf.jogos.2025.sub23.rodada05', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada05-18052025');})->name('tanabisaf.jogos.2025.sub23.rodada05');
 Route::get('tanabisaf.jogos.2025.sub23.rodada06', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada06-25052025');})->name('tanabisaf.jogos.2025.sub23.rodada06');
Route::get('tanabisaf/jogos/2025/sub23/rodada07', function () {
    return view('tanabisaf.jogos.2025.sub23.rodada07-31052025');
})->name('tanabisaf.jogos.2025.sub23.rodada07');
Route::get('tanabisaf.jogos.2025.sub23.rodada09', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada09-13062025');})
    ->name('tanabisaf.jogos.2025.sub23.rodada09');
Route::get('tanabisaf.jogos.2025.sub23.rodada10', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada10-21062025');})
    ->name('tanabisaf.jogos.2025.sub23.rodada10');
    Route::get('tanabisaf.jogos.2025.sub23.rodada11', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada11-27062025');})
    ->name('tanabisaf.jogos.2025.sub23.rodada11');
    Route::get('tanabisaf.jogos.2025.sub23.rodada12', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada12-05072025');})
    ->name('tanabisaf.jogos.2025.sub23.rodada12');
    Route::get('tanabisaf.jogos.2025.sub23.rodada13', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada13-12072025');})
    ->name('tanabisaf.jogos.2025.sub23.rodada13');
    Route::get('tanabisaf.jogos.2025.sub23.rodada14', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada14-19072025');})
    ->name('tanabisaf.jogos.2025.sub23.rodada14');
    Route::get('tanabisaf.jogos.2025.sub23.rodada15', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada15-26072025');})
    ->name('tanabisaf.jogos.2025.sub23.rodada15');
    Route::get('tanabisaf.jogos.2025.sub23.rodada16', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada16-02082025');})
    ->name('tanabisaf.jogos.2025.sub23.rodada16');
    Route::get('tanabisaf.jogos.2025.sub23.rodada17', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada17-10082025');})
    ->name('tanabisaf.jogos.2025.sub23.rodada17');
    Route::get('tanabisaf.jogos.2025.sub23.rodada18', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada18-16082025');})
    ->name('tanabisaf.jogos.2025.sub23.rodada18');
    Route::get('tanabisaf.jogos.2025.sub23.rodada19', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada19-23082025');})
    ->name('tanabisaf.jogos.2025.sub23.rodada19');
    Route::get('tanabisaf.jogos.2025.sub23.rodada20', function ()
    { return view('tanabisaf.jogos.2025.sub23.rodada20-30082025');})
    ->name('tanabisaf.jogos.2025.sub23.rodada20');


 Route::get('tanabisaf.jogos.2025.sub23.GolsMacola2025', function ()
 { return view('tanabisaf.jogos.2025.sub23.GolsMacola2025');})
    ->name('tanabisaf.jogos.2025.sub23.GolsMacola2025');
 Route::get('tanabisaf.jogos.2025.sub23.GolsDiogo2025', function ()
 { return view('tanabisaf.jogos.2025.sub23.GolsDiogo2025');})
    ->name('tanabisaf.jogos.2025.sub23.GolsDiogo2025');
 Route::get('tanabisaf.jogos.2025.sub23.GolsMaicon2025', function ()
 { return view('tanabisaf.jogos.2025.sub23.GolsMaicon2025');})
    ->name('tanabisaf.jogos.2025.sub23.GolsMaicon2025');
 Route::get('tanabisaf.jogos.2025.sub23.GolsThales2025', function ()
 { return view('tanabisaf.jogos.2025.sub23.GolsThales2025');})
    ->name('tanabisaf.jogos.2025.sub23.GolsThales2025');






    Route::get('tanabisaf.localtreino', function ()
    { return view('tanabisaf.localtreino');})->name('tanabisaf.localtreino');
    Route::get('tanabisaf.certidoes', function ()
    { return view('tanabisaf.certidoes');})->name('tanabisaf.certidoes');
    Route::get('tanabisaf.pde', function ()
    { return view('tanabisaf.pde');})->name('tanabisaf.pde');




# Irmaos_EmausPia
    Route::resource('Irmaos_EmausPia', App\Http\Controllers\Irmaos_EmausPiaController::class);
# Irmaos_EmausServicos
    Route::resource('Irmaos_EmausServicos', App\Http\Controllers\Irmaos_EmausServicosController::class);
# Irmaos_EmausServicos
    Route::resource('Irmaos_Emaus_FichaControle', App\Http\Controllers\Irmaos_Emaus_FichaControleController::class);
    Route::get('Irmaos_Emaus_EntradaSaida/{id}',  [App\Http\Controllers\Irmaos_Emaus_FichaControleController::class, 'EntradaSaida'])->name('Irmaos_EmausServicos.EntradaSaida');
    Route::post('GravaEntradaSaida', [App\Http\Controllers\Irmaos_Emaus_FichaControleController::class, 'GravaEntradaSaida'])->name('Irmaos_EmausServicos.GravaEntradaSaida');
    Route::get('Irmaos_Emaus_ListaEntradaSaida/{id}',  [App\Http\Controllers\Irmaos_Emaus_FichaControleController::class, 'ListaEntradaSaida'])
    ->name('Irmaos_Emaus_FichaControle.ListaEntradaSaida');


# Irmaos_Emaus_FichaControleArquivo
Route::put('ficha-controle/arquivo/{arquivo}', [Irmaos_Emaus_FichaControleArquivoController::class, 'update'])
->name('irmaos_emaus.ficha_controle_arquivo.update');


Route::get('ficha-controle/arquivo/{arquivo}/edit', [Irmaos_Emaus_FichaControleArquivoController::class, 'edit'])
->name('irmaos_emaus.ficha_controle_arquivo.edit');


Route::post('ficha-controle/{ficha}/arquivos', [Irmaos_Emaus_FichaControleArquivoController::class, 'store'])
    ->name('irmaos_emaus.ficha_controle_arquivo.store');

Route::get('ficha-controle/{ficha}/arquivos', [Irmaos_Emaus_FichaControleArquivoController::class, 'index'])
    ->name('irmaos_emaus.ficha_controle_arquivo.index');

Route::delete('ficha-controle/arquivo/{id}', [Irmaos_Emaus_FichaControleArquivoController::class, 'destroy'])->name('irmaos_emaus.ficha_controle_arquivo.destroy');

# Irmaos_Emaus_FichaControle
    Route::get('Irmaos_Emaus_FichaControle.showEnviarArquivos/{id}',  [App\Http\Controllers\Irmaos_Emaus_FichaControleController::class, 'showenviarArquivos'])
    ->name('Irmaos_Emaus_FichaControle.showenviarArquivos');

    Route::get('Irmaos_Emaus_RelatorioPia/{id}',  [App\Http\Controllers\Irmaos_Emaus_FichaControleController::class, 'RelatorioPia'])
    ->name('Irmaos_Emaus_FichaControle.RelatorioPia');
    Route::post('Irmaos_Emaus_ListaRelatorioPiaTopico/{id}',  [App\Http\Controllers\Irmaos_Emaus_FichaControleController::class, 'ListaRelatorioPiaTopico'])
    ->name('Irmaos_Emaus_FichaControle.ListaRelatorioPiaTopico');
    Route::get('Irmaos_Emaus_ListaRelatorioPia/{id}',  [App\Http\Controllers\Irmaos_Emaus_FichaControleController::class,
        'ListaRelatorioPia'])->name('Irmaos_Emaus_FichaControle.ListaRelatorioPia');
    Route::post('GravaRelatorioPia', [App\Http\Controllers\Irmaos_Emaus_FichaControleController::class, 'GravaRelatorioPia'])
   ->name('Irmaos_Emaus_FichaControle.GravaRelatorioPia');



//política de privacidade e termos de serviços
Route::get('webhook/politicaprivacidade', function ()
 { return view('webhook.politicaprivacidade');})->name('webhook.politicaprivacidade');
 Route::get('webhook/termoservicos', function ()
 { return view('webhook.termoservicos');})->name('webhook.termoservicos');

//Para autenticar no sistema sem usuario ou com usuário do google
Route::get('auth/google/', [GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('auth/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('auth.register');
Route::middleware('auth')->group(function () {


# PDF
Route::get('pdf/GerarPDF', [App\Http\Controllers\ExtratoConectCarController::class, 'GerarPDF'])->name('pdf.gerarpdf');


    #GoogleDrive
    Route::get('drive/showGoogleClientInfo', [App\Http\Controllers\GoogleDriveController::class, 'showGoogleClientInfo'])->name('google.showGoogleClientInfo');
    Route::get('drive/google/login', [App\Http\Controllers\GoogleDriveController::class, 'googleLogin'])->name('google.login');
    Route::post('drive/google-drive/file-upload', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileUpload'])->name('google.drive.file.upload');
    Route::get('drive/google-drive/file-uploadWhatsapp/{id}', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileUploadWhatsapp'])->name('google.drive.file.uploadWhatsapp');

    Route::post('drive/google-drive/file-delete', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileDelete'])->name('google.drive.file.delete');
    Route::post('drive/google-drive/file-deletedefinitivo', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileDeleteDefinitivo'])->name('google.drive.file.deletedefinitivo');
    Route::post('drive/google-drive/file-consultar', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileConsultar'])->name('google.drive.file.consultar');
    Route::get('drive/google-drive/file-consultardocumento/{id}', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileConsultarDocumento'])->name('google.drive.file.consultardocumento');


    Route::post('drive/google-drive/file-comentario', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileComentario'])->name('google.drive.file.comentario');
    Route::post('drive/google-drive/file-mover', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileMover'])->name('google.drive.file.mover');
    Route::post('drive/google-drive/file-alterarnome', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileAlterarNome'])->name('google.drive.file.alterarnome');



    Route::get('drive/dashboard', [App\Http\Controllers\GoogleDriveController::class, 'dashboard'])->name('googledrive.dashboard');
    Route::get('drive/DadosClienteGoogle', function () { return view('GoogleDrive.DadosClienteGoogle');})->name('Dados.clienteGoogle');
    // Route::get('drive/UploadArquivo', [App\Http\Controllers\GoogleDriveController::class, ' '])->name('google.uploadarquivo');
    Route::get('drive/UploadArquivo', function () { return view('GoogleDrive.UploadArquivoGoogleDrive');})->name('upload.arquivos');
    Route::get('Api/UploadGoogleDrive', function () { return view('Api.atendimento.UploadGoogleDrive');})->name('whatsapp.UploadGoogleDrive');

    Route::get('drive/DeleteArquivo', function () { return view('GoogleDrive.DeleteArquivoGoogleDrive');})->name('delete.arquivos');
    Route::get('drive/DeleteArquivoDefinitivo', function () { return view('GoogleDrive.DeleteDefinitivoArquivoGoogleDrive');})->name('deletedefinitivo.arquivos');
    Route::get('drive/ConsultarArquivo', function () { return view('GoogleDrive.ConsultarArquivoGoogleDrive');})->name('consultar.arquivos');

    Route::get('drive/ComentarioArquivo', function () { return view('GoogleDrive.ComentarioArquivoGoogleDrive');})->name('comentario.arquivos');
    Route::get('drive/MoverArquivo', function () { return view('GoogleDrive.MoverArquivoGoogleDrive');})->name('mover.arquivos');
    Route::get('drive/AlterarNomeArquivo', function () { return view('GoogleDrive.AlterarNomeArquivoGoogleDrive');})->name('alterarnome.arquivos');
    Route::get('drive/InformacaoArquivo', function () { return view('GoogleDrive.InformacaoGoogleDrive');})->name('informacao.arquivos');

    #Rotas criadas automaticamente laravel
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    # ENVIA EMAIL GMAIL
    // Route::get('/enviar-email', function () {
    //     $sender = new App\Services\GmailSender();
    //     $sender->send('usuario@example.com', 'Assunto do e-mail', 'Corpo da mensagem do e-mail');
    //     return "E-mail enviado com sucesso!";
    // });

    //autenticação google paraenvio de email
    Route::prefix('/mail')->group(function () {
        Route::view('home', 'mail.home')->name('mail.home');

        Route::post('/get-token', [OAuthController::class, 'doGenerateToken'])->name('generate.token');
        Route::get('/get-token', [OAuthController::class, 'doSuccessToken'])->name('token.success');
        Route::post('/send', [MailController::class, 'send'])->name('send.email');
    });

    // Route::get('EnviarEmail', [App\Http\Controllers\EnviaEmailController::class, 'enviaremail'])->name('gmail.enviaremail');
    // Route::get('GoogleLogin', [App\Http\Controllers\EnviaEmailController::class, 'googlelogin'])->name('google.login');

    # Feriados
    Route::resource('Feriados', App\Http\Controllers\FeriadoController::class);

    # Centro de custos
    Route::post('ContasCentroCustos/gerarCalculoPDFPeriodo', [App\Http\Controllers\ContasCentroCustosController::class, 'gerarCalculoPDFPeriodo'])->name('ContasCentroCustos.gerarCalculoPDFPeriodo');
    Route::get('ContasCentroCustos/gerarCalculoPDF/{id?}', [App\Http\Controllers\ContasCentroCustosController::class, 'gerarCalculoPDF'])->name('ContasCentroCustos.gerarCalculoPDF');
    Route::get('CentroCustos/dashboard', [App\Http\Controllers\CentroCustosController::class, 'dashboard'])->name('CentroCustos.dashboard');
    Route::get('ContasCentroCustos/calculocontascentrocustos/{id?}', [App\Http\Controllers\ContasCentroCustosController::class, 'CalculoContasCentroCustos'])->name('ContasCentroCustos.calculocontascentrocustos');
    Route::resource('CentroCustos', App\Http\Controllers\CentroCustosController::class);
    Route::resource('ContasCentroCustos', App\Http\Controllers\ContasCentroCustosController::class);


 # Contas a pagar
 Route::match(['get', 'post'], 'contaspagar/index', [App\Http\Controllers\ContasPagarController::class, 'index'])->name('contaspagar.index');
 Route::post('ContasPagar/CreateArquivoContasPagar', [App\Http\Controllers\ContasPagarController::class, 'CreateArquivoContasPagar'])->name('contaspagar.ArquivoContasPagar');
 Route::resource('ContasPagar', App\Http\Controllers\ContasPagarController::class);
 Route::post('ContasPagar/indexpost', [App\Http\Controllers\ContasPagarController::class, 'indexpost'])->name('contaspagar.index.post');
 Route::post('ContasPagar/alterarvalormultiplos', [App\Http\Controllers\ContasPagarController::class, 'alterarvalormultiplos'])->name('contaspagar.alterarvalormultiplos');
 Route::get('ContasPagar/IncluirLancamentoContasPagar/{id}',[App\Http\Controllers\ContasPagarController::class,'IncluirLancamentoContasPagar'])->name('contaspagar.IncluirLancamentoContasPagar');


 # Clientes IXC
 Route::resource('ClientesIxc', App\Http\Controllers\ClientesIxcController::class);
 Route::get('/Ixc', function () { return view('Ixc.dashboard');})->name('ixc.dashboard');
 Route::get('Ixc/Clientes/dashboard', [App\Http\Controllers\ClientesIxcController::class, 'dashboard'])->name('ClientesIxc.dashboard');
 Route::get('Ixc/Clientes/contratostv', [App\Http\Controllers\ClientesIxcController::class, 'contratos_ixc_tv'])->name('ClientesIxc.contratos_ixc_tv');
 Route::get('Ixc/Clientes/contratosapp', [App\Http\Controllers\ClientesIxcController::class, 'contratos_ixc_app'])->name('ClientesIxc.contratos_ixc_app');
 Route::get('Ixc/Clientes/contratosHBO', [App\Http\Controllers\ClientesIxcController::class, 'contratos_ixc_HBO'])->name('ClientesIxc.contratos_ixc_HBO');


 # Receber IXC
 Route::resource('ReceberIxc', App\Http\Controllers\ReceberIxcController::class);
 Route::get('Ixc/Receber/dashboard', [App\Http\Controllers\ReceberIxcController::class, 'dashboard'])->name('ReceberIxc.dashboard');
 Route::get('Ixc/Receber/receberperiodo', [App\Http\Controllers\ReceberIxcController::class, 'receberperiodo'])->name('ReceberIxc.receberperiodo');

 # Contatos do Whatsapp
 Route::resource('ContatosWhatsapp', App\Http\Controllers\ContatosWhatsappController::class);
 Route::get('contatos/temposessao',[App\Http\Controllers\ContatosWhatsappController::class,'temposessaocontato'])->name('temposessaocontato.temposessao');
 Route::get('contatos/indexbuscar',[App\Http\Controllers\ContatosWhatsappController::class,'indexbuscar'])->name('contatos.indexbuscar');

 # API WHATSAPP
 Route::get('/whatsapp/TransferirAtendimento/{id}', [App\Http\Controllers\ApiController::class, 'TransferirAtendimento'])->name('whatsapp.TransferirAtendimento');
 Route::get('/whatsapp/CancelarTransferirAtendimento/{id}', [App\Http\Controllers\ApiController::class, 'CancelarTransferirAtendimento'])->name('whatsapp.CancelarTransferirAtendimento');

 Route::get('/whatsapp/ConfirmaRecebimentoMensagem/{id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'ConfirmaRecebimentoMensagem'])->name('whatsapp.ConfirmaRecebimentoMensagem');

 Route::get('/whatsapp/Enviar_Arquivo', [App\Http\Controllers\ApiController::class,
 'Enviar_Arquivo'])->name('whatsapp.Enviar_Arquivo');

 Route::get('/whatsapp/enviarMensagemAprovadaAriane',
 [App\Http\Controllers\ApiController::class, 'enviarMensagemAprovadaAriane'])
 ->name('whatsapp.enviarMensagemAprovadaAriane');

 Route::get('/whatsapp/enviarMensagemAprovadaAngelica',
  [App\Http\Controllers\ApiController::class,  'enviarMensagemAprovadaAngelica'])
  ->name('whatsapp.enviarMensagemAprovadaAngelica');

  Route::get('/whatsapp/StatusMensagemEnviada/{id}', [App\Http\Controllers\ApiController::class, 'StatusMensagemEnviada'])
  ->name('whatsapp.StatusMensagemEnviada');

  Route::get('/storage/whatsapp/{filename}', function ($filename) {
    // Coloque aqui a lógica para lidar com a requisição, como o envio do arquivo ou redirecionamento para ele.
    // Você pode usar a função `response()->file()` para enviar o arquivo.
    return response()->file('../storage/whatsapp/' . $filename);
})->where('filename', '.*');

Route::get('/storage/arquivos/{filename}', function ($filename) {
    // Coloque aqui a lógica para lidar com a requisição, como o envio do arquivo ou redirecionamento para ele.
    // Você pode usar a função `response()->file()` para enviar o arquivo.
    return response()->file('../storage/app/arquivos/' . $filename);
})->where('filename', '.*');


 Route::get('/whatsapp/registroresposta', function () { return view('api.registroresposta');})->name('whatsapp.registroresposta');
 Route::get('/whatsapp/enviarMensagemNova', [App\Http\Controllers\ApiController::class, 'enviarMensagemNova'])->name('whatsapp.enviarMensagemNova');
 Route::get('/whatsapp/PreencherMensagemResposta/{id}', [App\Http\Controllers\ApiController::class, 'PreencherMensagemResposta'])
 ->name('whatsapp.PreencherMensagemResposta');
 Route::get('/whatsapp/SelecionarMensagemAprovada', [App\Http\Controllers\ApiController::class, 'SelecionarMensagemAprovada'])->name('whatsapp.SelecionarMensagemAprovada');

 Route::post('/whatsapp/refreshpagina/{id}/{entry_id}', [App\Services\WebhookServico::class, 'refreshpagina'])->name('whatsapp.refreshpagina');
 Route::post('/whatsapp/carregamentomultimidia/{id}/{entry_id}', [App\Services\WebhookServico::class, 'carregamentomultimidia'])->name('whatsapp.carregamentomultimidia');

 Route::get('/whatsapp/PesquisaMensagens/{id}', [App\Services\WebhookServico::class, 'PesquisaMensagens'])->name('whatsapp.PesquisaMensagens');

 Route::post('/whatsapp/enviarMensagemAprovada', [App\Http\Controllers\ApiController::class, 'enviarMensagemAprovada'])->name('whatsapp.enviarMensagemAprovada');
 Route::post('/whatsapp/enviarMensagemRespostaAtendimento/{id}', [App\Http\Controllers\ApiController::class, 'enviarMensagemRespostaAtendimento'])->name('whatsapp.enviarMensagemRespostaAtendimento');
 Route::post('/whatsapp/enviarMensagemResposta/{id}', [App\Http\Controllers\ApiController::class, 'enviarMensagemResposta'])->name('whatsapp.enviarMensagemResposta');
 Route::post('/whatsapp/enviarMensagemEncerramentoAtendimento/{id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'enviarMensagemEncerramentoAtendimento'])
 ->name('whatsapp.enviarMensagemEncerramentoAtendimento');

 Route::post('/whatsapp/enviarMensagemEncerramentoAtendimentoSemAviso/{id}', [App\Http\Controllers\ApiController::class,
  'enviarMensagemEncerramentoAtendimentoSemAviso'])->name('whatsapp.enviarMensagemEncerramentoAtendimentoSemAviso');;


 Route::post('/whatsapp/enviarMensagemInicioAtendimento/{id}', [App\Http\Controllers\ApiController::class, 'enviarMensagemInicioAtendimento'])
 ->name('whatsapp.enviarMensagemInicioAtendimento');

 Route::post('/whatsapp/ ReabrirAposEncerramentoAtendimento/{id}', [App\Http\Controllers\ApiController::class, 'ReabrirAposEncerramentoAtendimento'])
 ->name('whatsapp.ReabrirAposEncerramentoAtendimento');


 Route::get('/whatsapp/atendimentoWhatsappBuscar', [App\Http\Controllers\ApiController::class, 'atendimentoWhatsappBuscar'])->name('whatsapp.atendimentoWhatsappBuscar');
 Route::get('/whatsapp/registro/{id}', [App\Http\Controllers\ApiController::class, 'registro'])->name('whatsapp.registro');
 Route::get('/whatsapp/atendimento/{id}', [App\Http\Controllers\ApiController::class, 'atendimento'])->name('whatsapp.atendimento');
 Route::get('/whatsapp/atendimentoWhatsapp', [App\Http\Controllers\ApiController::class, 'atendimentoWhatsapp'])->name('whatsapp.atendimentoWhatsapp');
 Route::get('/whatsapp/atendimentoWhatsappFiltroTelefone/{recipient_id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'atendimentoWhatsappFiltroTelefone'])->name('whatsapp.atendimentoWhatsappFiltroTelefone');
 Route::get('/whatsapp/enviarFlowAlterarCPF/{recipient_id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'enviarFlowAlterarCPF'])->name('whatsapp.enviarFlowAlterarCPF');
 Route::get('/whatsapp/enviarFlowAlterarRG/{recipient_id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'enviarFlowAlterarRG'])->name('whatsapp.enviarFlowAlterarRG');
 Route::get('/whatsapp/enviarFlowAlterarCidadeUf/{recipient_id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'enviarFlowAlterarCidadeUf'])->name('whatsapp.enviarFlowAlterarCidadeUf');
 Route::get('/whatsapp/enviarFlowAlterarNomeCompleto/{recipient_id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'enviarFlowAlterarNomeCompleto'])->name('whatsapp.enviarFlowAlterarNomeCompleto');
 Route::get('/whatsapp/enviarFlowCadastro/{recipient_id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'enviarFlowCadastro'])->name('whatsapp.enviarFlowCadastro');
 Route::get('/whatsapp/enviarFlowMenuCadastroBasico/{recipient_id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'enviarFlowMenuCadastroBasico'])->name('whatsapp.enviarFlowMenuCadastroBasico');
 Route::get('/whatsapp/enviarFlowAlterarNascimento/{recipient_id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'enviarFlowAlterarNascimento'])->name('whatsapp.enviarFlowAlterarNascimento');
 Route::get('/whatsapp/enviarFlowAlterarNomeMae/{recipient_id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'enviarFlowAlterarNomeMae'])->name('whatsapp.enviarFlowAlterarNomeMae');
 Route::get('/whatsapp/enviarFlowAlterarNomePai/{recipient_id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'enviarFlowAlterarNomePai'])->name('whatsapp.enviarFlowAlterarNomePai');
Route::get('/whatsapp/EnviaMensagemDadosCadastroBasico/{recipient_id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'EnviaMensagemDadosCadastroBasico'])->name('whatsapp.EnviaMensagemDadosCadastroBasico');
Route::get('/whatsapp/enviarFlowAvaliacao29012024_01032024/{recipient_id}/{entry_id}', [App\Http\Controllers\ApiController::class, 'enviarFlowAvaliacao29012024_01032024'])->name('whatsapp.enviarFlowAvaliacao29012024_01032024');


 Route::get('/whatsapp/atualizaregistro/{id}', [App\Http\Controllers\ApiController::class, 'atualizaregistro'])->name('whatsapp.atualizaregistro');
 Route::get('/whatsapp', [App\Http\Controllers\ApiController::class,'indexlista']);
 Route::get('/whatsapp/indexlista', [App\Http\Controllers\ApiController::class, 'indexlista'])->name('whatsapp.indexlista');

 Route::get('/whatsapp/salvararquivoPostWebhook', [App\Http\Controllers\ApiController::class, 'salvararquivoPostWebhook'])->name('whatsapp.salvararquivoPostWebhook');

 Route::get('/whatsapp/Pegar_URL_Arquivo/{id}/{entry_id}', [App\Http\Controllers\ApiController::class,
 'Pegar_URL_Arquivo'])->name('whatsapp.Pegar_URL_Arquivo');
 Route::get('/whatsapp/ConvidarMensagemAprovada/{id}', [App\Http\Controllers\ApiController::class,
 'ConvidarMensagemAprovada'])->name('whatsapp.ConvidarMensagemAprovada');


 Route::resource('Templates', App\Http\Controllers\WebhookTemplateController::class);

Route::resource('WebhookConfig', App\Http\Controllers\WebhookConfigController::class);

 # Formandos base

 Route::post('FormandoBase/indexBusca', [App\Http\Controllers\FormandoBaseController::class, 'indexBusca'])->name('FormandoBase.indexBusca');
 Route::post('FormandoBase/CreateArquivoFormandoBase', [App\Http\Controllers\FormandoBaseController::class, 'CreateArquivoFormandoBase'])->name('FormandoBase.ArquivoFormandoBase');
 Route::post('FormandoBase/CreateRecebimentoFormandoBase', [App\Http\Controllers\FormandoBaseController::class, 'CreateRecebimentoFormandoBase'])->name('FormandoBase.RecebimentoFormandoBase');
 Route::post('FormandoBase/CreateRedeSocialFormandoBase', [App\Http\Controllers\FormandoBaseController::class, 'CreateRedeSocialFormandoBase'])->name('FormandoBase.RedeSocialFormandoBase');
 Route::post('FormandoBase/CreatePosicaoFormandoBase', [App\Http\Controllers\FormandoBaseController::class, 'CreatePosicaoFormandoBase'])->name('FormandoBase.PosicaoFormandoBase');
 Route::post('FormandoBase/CreateAvaliacaoFormandoBase', [App\Http\Controllers\FormandoBaseController::class, 'CreateAvaliacaoFormandoBase'])->name('FormandoBase.AvaliacaoFormandoBase');
 Route::get('FormandoBase/Excluidos', [App\Http\Controllers\FormandoBaseController::class, 'Excluidos'])->name('formandobase.excluidos');
Route::get('FormandoBase/ConsultaEmpresa', [App\Http\Controllers\FormandoBaseController::class, 'ConsultaEmpresa'])->name('formandobase.consultaempresa');
Route::resource('FormandoBase', App\Http\Controllers\FormandoBaseController::class);
Route::resource('FormandoBasePosicoes', App\Http\Controllers\FormandoBasePosicoesController::class);
Route::resource('FormandoBaseArquivos', App\Http\Controllers\FormandoBaseArquivosController::class);
Route::resource('FormandoBaseAvaliacao', App\Http\Controllers\FormandoBaseAvaliacaoController::class);


 # Formandos base whatsapp - cadastros via flow
 Route::post('FormandoBaseWhatsapp/indexBusca', [App\Http\Controllers\FormandoBaseWhatsappController::class, 'indexBusca'])->name('FormandoBaseWhatsapp.indexBusca');
 Route::post('FFormandoBaseWhatsapp.AtualizaIdade/AtualizaIdade', [App\Http\Controllers\FormandoBaseWhatsappController::class, 'AtualizaIdade'])->name('FormandoBaseWhatsapp.AtualizaIdade');
 Route::post('FormandoBaseWhatsapp.AtualizaWatsapp', [App\Http\Controllers\FormandoBaseWhatsappController::class, 'AtualizaWatsapp'])->name('FormandoBaseWhatsapp.AtualizaWatsapp');
 Route::resource('FormandoBaseWhatsapp', App\Http\Controllers\FormandoBaseWhatsappController::class);


# Formandos base recebimentos
Route::get('FormandoBase/ConsultaFormandoBaseRecebimento/{id}', [App\Http\Controllers\FormandoBaseRecebimentosController::class, 'ConsultaFormandoBaseRecebimento'])->name('formandoBase.ConsultaFormandoBaseRecebimento');;
Route::resource('FormandoBaseRecebimentos', App\Http\Controllers\FormandoBaseRecebimentosController::class);


 # Representantes
    Route::post('Representantes/CreateRedeSocialRepresentantes', [App\Http\Controllers\RepresentantesController::class, 'CreateRedeSocialRepresentantes'])->name('representantes.RedeSocialRepresentantes');
    Route::get('Representantes/RepresentantesCadastro', [App\Http\Controllers\RepresentantesController::class, 'representantecadastro'])->name('representantes.cadastrorepresentante');
    Route::resource('Representantes', App\Http\Controllers\RepresentantesController::class);

 #PAC PIE
  Route::resource('Pacpie', App\Http\Controllers\PacpieController::class);


  Route::get('Pacpie/go-back-twice-and-refresh', [App\Http\Controllers\PacpieController::class, 'retornar2paginasatualizar']);
  Route::get('MarcaEnviadoemailparaprimeirocontato/{id}', [App\Http\Controllers\PacpieController::class, 'MarcaEnviadoemailparaprimeirocontato'])->name('Pacpie.MarcaEnviadoemailparaprimeirocontato');
  Route::get('MarcaRetornoEnviadoemailparaprimeirocontato/{id}', [App\Http\Controllers\PacpieController::class, 'MarcaRetornoEnviadoemailparaprimeirocontato'])->name('Pacpie.MarcaRetornoEnviadoemailparaprimeirocontato');
  Route::get('Marcaemailcomfalhas/{id}', [App\Http\Controllers\PacpieController::class, 'Marcaemailcomfalhas'])->name('Pacpie.Marcaemailcomfalhas');
  Route::get('indexSelecao', [App\Http\Controllers\PacpieController::class, 'indexSelecao'])->name('Pacpie.indexSelecao');
  Route::post('BuscarTexto', [App\Http\Controllers\PacpieController::class, 'BuscarTexto'])->name('Pacpie.BuscarTexto');
  Route::post('Pacpie/AjustaCampos', [PacpieController::class, 'AjustaCampos'])->name('Pacpie.AjustaCampos');

#ORIGEM DE EMPRESAS PARA PAC PIE
Route::resource('OrigemPacpie', App\Http\Controllers\OrigemPacpieController::class);
Route::post('OrigemPacpie/AjustaCampos', [OrigemPacpieController::class, 'AjustaCampos'])->name('OrigemPacpie.AjustaCampos');

#TipoFormandoBaseWhatsapp
Route::resource('TipoFormandoBaseWhatsapp', App\Http\Controllers\TipoFormandoBaseWhatsappController::class);
// Route::post('OrigemPacpie/AjustaCampos', [OrigemPacpieController::class, 'AjustaCampos'])->name('OrigemPacpie.AjustaCampos');




    # ENERGIA INJETADA
Route::get('EnergiaInjetada/dashboard', [App\Http\Controllers\EnergiaInjetada\EnergiaInjetadaController::class, 'dashboard'])->name('EnergiaInjetada.dashboard');
Route::resource('EnergiaInjetada', App\Http\Controllers\EnergiaInjetada\EnergiaInjetadaController::class);


# Cargo Profissional
Route::resource('CargoProfissional', App\Http\Controllers\CargoProfissionalController::class);

# Função Profissional
Route::resource('FuncaoProfissional', App\Http\Controllers\FuncaoProfissionalController::class);

 # Representantes
 Route::resource('TipoRepresentantes', App\Http\Controllers\TipoRepresentanteController::class);


 # Tipo de esportes
Route::resource('TipoEsporte', App\Http\Controllers\TipoEsporteController::class);

 # Tipo de arquivos
 Route::resource('TipoArquivo', App\Http\Controllers\TipoArquivoController::class);

 # Preparadores
 Route::post('Preparadores/CreateArquivoPreparadores', [App\Http\Controllers\PreparadoresController::class, 'CreateArquivoPreparadores'])->name('Preparadores.ArquivoPreparadores');
 Route::resource('Preparadores', App\Http\Controllers\PreparadoresController::class);
 Route::resource('PreparadoresArquivos', App\Http\Controllers\PreparadoresArquivosController::class);


# Agrupamentos de contas
Route::resource('AgrupamentosContas', App\Http\Controllers\AgrupamentosContasController::class);

 # Posicoes
 Route::resource('Posicoes', App\Http\Controllers\PosicoesController::class);

 # Categorias
 Route::resource('Categorias', App\Http\Controllers\CategoriasController::class);

# Redes sociais
Route::resource('RedeSocial', App\Http\Controllers\RedeSocialController::class);

# Redes sociais usuários
Route::resource('RedeSocialUsuarios', App\Http\Controllers\RedeSocialUsuarioController::class);

    # PLANO DE CONTAS
    Route::get('PlanoContas/pesquisaavancada', [App\Http\Controllers\GoogleCalendarController::class, 'pesquisaavancada'])->name('planocontas.pesquisaavancada');

    #testes
    Route::get('Agenda', [\App\Http\Controllers\TesteController::class, 'googleAgenda']);

    #GoogleCalendar
    Route::get('Agenda/starthoje', [App\Http\Controllers\GoogleCalendarController::class, 'starthoje'])->name('Agenda.starthoje');
    Route::get('Agenda/startanterior', [App\Http\Controllers\GoogleCalendarController::class, 'startanterior'])->name('Agenda.startanterior');
    Route::get('Agenda/startposterior', [App\Http\Controllers\GoogleCalendarController::class, 'startposterior'])->name('Agenda.startposterior');
    Route::get('Agenda/dashboard/{token?}', [App\Http\Controllers\GoogleCalendarController::class, 'dashboard'])->name('google.dashboard');
    Route::resource('Agenda', App\Http\Controllers\GoogleCalendarController::class);

    #Empresas

    Route::put('Empresas/desbloquearempresas', [App\Http\Controllers\EmpresaController::class, 'desbloquearempresas'])->name('Empresas.DesbloquearEmpresas');
    Route::put('Empresas/bloquearempresas', [App\Http\Controllers\EmpresaController::class, 'bloquearempresas'])->name('Empresas.BloquearEmpresas');
    Route::put('Empresas/desbloquearempresas', [App\Http\Controllers\EmpresaController::class, 'desbloquearempresas'])->name('Empresas.DesbloquearEmpresas');
    Route::resource('Empresas', App\Http\Controllers\EmpresaController::class);

    #Gerenciamento de Usuários
    Route::resource('Usuarios', App\Http\Controllers\UserController::class);
    Route::post('Usuarios/salvarpermissao/{id}', [App\Http\Controllers\UserController::class, 'salvarpermissao']);
    Route::post('Usuarios/salvarfuncao/{id}', [App\Http\Controllers\UserController::class, 'salvarfuncao']);
    Route::post('Usuarios/salvar-empresa/{id}', [App\Http\Controllers\UserController::class, 'salvarEmpresa']);

    #Gerenciamento de permissões e funções
    Route::resource('Permissoes', App\Http\Controllers\PermissionController::class);
    // Route::resource('ModelodeFuncoes', App\Http\Controllers\Model_has_RoleController::class);

    #PlanoContas
    Route::get('PlanoContas/pesquisaavancada', [App\Http\Controllers\PlanoContaController::class, 'pesquisaavancada'])->name('planocontas.pesquisaavancada');
    Route::post('PlanoContas/pesquisaavancada', [App\Http\Controllers\PlanoContaController::class, 'pesquisaavancadapost'])->name('planocontas.pesquisaavancada.post');
    Route::get('PlanoContas/autenticar/{EmpresaID}', [App\Http\Controllers\EmpresaController::class, 'autenticar'])->name('planocontas.autenticar');
    Route::get('PlanoContas/dashboard', [App\Http\Controllers\PlanoContaController::class, 'dashboard'])->name('planocontas.dashboard');
    Route::post('PlanoContas/BalanceteEmpresa', [App\Http\Controllers\PlanoContaController::class, 'BalanceteEmpresa'])->name('planocontas.balanceteempresa');
    Route::get('PlanoContas/Balancetes', [App\Http\Controllers\PlanoContaController::class, 'Balancetes'])->name('planocontas.balancetes');
    Route::get('PlanoContas/Balancetespdf', [App\Http\Controllers\PlanoContaController::class, 'Balancetesgerarpdf'])->name('planocontas.Balancetesgerarpdf');
    Route::resource('PlanoContas', App\Http\Controllers\PlanoContaController::class);
    Route::post('PlanoContas/FiltroAgrupamento', [App\Http\Controllers\PlanoContaController::class, 'FiltroAgrupamento'])->name('planocontas.FiltroAgrupamento');

    #Lançamentos


    Route::get('lancamentos/ExportarExtratoExcel',[App\Http\Controllers\LancamentosController::class,'ExportarExtratoExcel'])->name('lancamentos.ExportarExtratoExcel');
    Route::post('Lancamentos/ExportarExtratoExcelpost',[App\Http\Controllers\LancamentosController::class,'ExportarExtratoExcelpost'])->name('lancamentos.ExportarExtratoExcelpost');

    Route::post('Lancamentos/ExportarSkalaExcelpost',[App\Http\Controllers\LancamentosController::class,'ExportarSkalaExcelpost'])->name('lancamentos.exportarskalaExcelpost');
    Route::post('Lancamentos/ExportarSkalapost',[App\Http\Controllers\LancamentosController::class,'ExportarSkalapost'])->name('lancamentos.exportarskalapost');
    Route::post('lancamentos/lancamentotabelaprice',[App\Http\Controllers\LancamentosController::class,'lancamentotabelaprice'])->name('lancamentos.lancamentotabelaprice');
    Route::get('lancamentos/exportarskala',[App\Http\Controllers\LancamentosController::class,'ExportarSkala'])->name('lancamentos.ExportarSkala');
    Route::get('lancamentos/exportarskalaexcel',[App\Http\Controllers\LancamentosController::class,'ExportarSkalaExcel'])->name('lancamentos.ExportarSkalaExcel');
    Route::get('Lancamentos/lancamentoinformaprice',[App\Http\Controllers\LancamentosController::class,'lancamentoinformaprice'])->name('lancamentos.lancamentoinformaprice');
    Route::get('Lancamentos/Informaprice',[App\Http\Controllers\LancamentosController::class,'Informaprice'])->name('lancamentos.informaprice');
    Route::post('lancamentos/tabelaprice',[App\Http\Controllers\LancamentosController::class,'tabelaprice'])->name('lancamentos.tabelaprice');
    Route::get('lancamentos/download/{id}',[App\Http\Controllers\LancamentosController::class,'baixarArquivo'])->name('lancamentos.download');
    Route::get('Lancamentos/tabelaprice', function () { return view('Lancamento.tabelaprice');})->name('lancamentos.tabelapriceresultado');
    Route::get('lancamentos/solicitacoes',[App\Http\Controllers\LancamentosController::class,'Solicitacoes'])->name('lancamentos.solicitacoes');
    Route::get('lancamentos/solicitacoesexcluir{id}',[App\Http\Controllers\LancamentosController::class,'SolicitacoesExcluir'])->name('lancamentos.solicitacoesexcluir');
    Route::get('lancamentos/solicitacoesTransferir{id}',[App\Http\Controllers\LancamentosController::class,'solicitacoesTransferir'])->name('lancamentos.solicitacoestransferir');


    #Contas
    Route::get('Contas/Extrato/{contaID}', [App\Http\Controllers\ContaController::class, 'extrato']);
    Route::get('Contas/GerarExtratoPDF', [App\Http\Livewire\Conta\Extrato::class, 'GerarExtratoPDF'])->name('Extrato.gerarpdf');
    Route::resource('Contas', App\Http\Controllers\ContaController::class);
    Route::resource('ContasCobranca', App\Http\Controllers\ContaCobrancaController::class);

    #Funções
    Route::resource('Funcoes', App\Http\Controllers\RoleController::class);
    Route::post('Funcoes/salvarpermissao/{id}', [App\Http\Controllers\RoleController::class, 'salvarpermissao']);

    #Moedas e valores
    Route::get('Moedas/dashboard', [App\Http\Controllers\MoedaController::class, 'dashboard'])->name('moedas.dashboard');
    Route::resource('Moedas', App\Http\Controllers\MoedaController::class);

    Route::resource('MoedasValores', App\Http\Controllers\MoedaValoresController::class);
    Route::post('Moedas/selecionar', [App\Http\Controllers\MoedaValoresController::class, 'selecionarMoeda'])->name('moedas.selecionar');

#LANCAMENTO - DOCUMENTO
// Route::get('Moedas/dashboard', [App\Http\Controllers\MoedaController::class, 'dashboard'])->name('moedas.dashboard');


Route::get('drivelocal/UploadArquivoLocal', function () { return view('LancamentosDocumentos.UploadArquivoServidorLocal');})->name('upload.arquivosLocal');
// Route::post('drive/google-drive/file-upload', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileUpload'])->name('google.drive.file.upload');
Route::post('drivelocal/file-upload', [App\Http\Controllers\LancamentosDocumentosController::class, 'DriveLocalFileUpload'])->name('drivelocal.file.upload');




Route::post('AtualizarDadosPoupanca', [App\Http\Controllers\LancamentosController::class, 'AtualizarDadosPoupanca'])->name('lancamentos.atualizardadospoupanca');


Route::get('Lancamentos.DadosGabrielMagossiFalchi', [LancamentosController::class, 'exibirDadosGabrielMagossiFalchi'])->name('lancamentos.exibirDadosGabrielMagossiFalchi');
Route::get('Lancamentos.AvenuePoupanca', [LancamentosController::class, 'exibirDadosAvenuePoupanca'])->name('lancamentos.avenuepoupanca');

Route::get('Lancamentos.AtualizarPoupanca', [LancamentosController::class, 'AtualizarSaldoPoupanca'])->name('lancamentos.atualizarpoupanca');


Route::get('Lancamentos.DadosMes', [LancamentosController::class, 'DadosMes'])->name('lancamentos.DadosMes');
// Route::get('Lancamentos.DadosGabrielMagossiFalchi', function () { return view('Lancamentos.DadosGabrielMagossiFalchi');})->name('Lancamentos.DadosGabrielMagossiFalchi');
Route::post('Lancamentos/createArquivoDocumentos', [App\Http\Controllers\LancamentosDocumentosController::class, 'createArquivoDocumentos'])->name('lancamentos.ArquivoLancamentoDocumentos');
Route::post('LancamentosDocumentos/pesquisaavancada', [App\Http\Controllers\LancamentosDocumentosController::class, 'pesquisaavancada'])->name('lancamentosdocumentos.pesquisaavancada');
Route::get('/lancamentosdocumentos/{id}',[App\Http\Controllers\LancamentosDocumentosController::class,'indexpost'])->name('LancamentosDocumentosID.index');
Route::resource('LancamentosDocumentos', App\Http\Controllers\LancamentosDocumentosController::class);

    #ARQUIVOS

    Route::get('LeituraArquivo/GerarPDF', [App\Http\Controllers\LeituraArquivoController::class, 'GerarPDF'])->name('LeituraArquivo.gerarpdf');
    Route::get('read-pdf', [PDFController::class, 'readPDF']);
    Route::post('LeituraArquivo/SelecionaDatas', [App\Http\Controllers\LeituraArquivoController::class, 'SelecionaDatas'])->name('LeituraArquivo.SelecionaDatas');
    Route::post('LeituraArquivo/SelecionaLinha', [App\Http\Controllers\LeituraArquivoController::class, 'SelecionaLinha'])->name('LeituraArquivo.SelecionaLinha');
    Route::post('LeituraArquivo/SelecionaDatasExtratoSicrediPJ', [App\Http\Controllers\LeituraArquivoController::class, 'SelecionaDatasExtratoSicrediPJ'])->name('LeituraArquivo.SelecionaDatasExtratoSicrediPJ');
    Route::post('LeituraArquivo/SelecionaDatasExtratoBradescoPJ', [App\Http\Controllers\LeituraArquivoController::class, 'SelecionaDatasExtratoBradescoPJ'])->name('LeituraArquivo.SelecionaDatasExtratoBradescoPJ');

    // Route::post('LeituraArquivo/SelecionaDatasFaturaEmAberto', [App\Http\Controllers\LeituraArquivoController::class, 'SelecionaDatasFaturaEmAberto'])->name('LeituraArquivo.SelecionaDatasFaturaEmAberto');
    Route::post('FaturaSicrediAberto/SelecionaDatasFaturaEmAberto', [App\Http\Controllers\FaturaCartaoCreditoSicrediAbertoController::class, 'SelecionaDatasFaturaEmAberto'])->name('FaturaSicrediAberto.SelecionaDatasFaturaEmAberto');
    Route::get('LeituraArquivo/SomenteLinha', function () { return view('LeituraArquivo.SomenteLinha');})->name('LeituraArquivo.SomenteLinha');
    Route::resource('LeituraArquivo', App\Http\Controllers\LeituraArquivoController::class);



    #TRADEIDEA
    Route::post('/salvarTradeidea', [App\Http\Controllers\TradeideaController::class,'salvarTradeidea'])->name('salvar.tradeidea');
    Route::post('Tradeidea/ImportaArquivoExcelTradeIdea', [App\Http\Controllers\TradeideaController::class,
                'ImportaArquivoExcelTradeIdea'])->name('Tradeidea.ImportaArquivoExcelTradeIdea');
    Route::get('Tradeidea/Importaexceltradeidea', function () { return view('Tradeidea.importarexceltradeidea');})->name('Tradeidea.importarexceltradeidea');
    Route::get('Tradeidea/Mostraexceltradeidea', function () { return view('Tradeidea.mostraexceltradeidea');})->name('Tradeidea.mostraexceltradeidea');
    Route::resource('Tradeidea', App\Http\Controllers\TradeideaController::class);





#PLANILHA EXCEL CAIXA
Route::post('Caixa/ExtratoCaixa', [App\Http\Controllers\ExtratoCaixaController::class, 'ExtratoCaixa'])->name('Caixa.ExtratoCaixa');


    #EXTRATO CONECTCAR
    Route::post('ConectCar/ExtratoConectCar', [App\Http\Controllers\ExtratoConectCarController::class, 'ExtratoConectar'])->name('ConectCar.ExtratoConectCar');

    #Faturamentos
    Route::get('Faturamentos/selecaoperiodoempresa', [App\Http\Controllers\FaturamentosController::class, 'selecaoperiodoempresa'])->name('Faturamentos.selecaoperiodoempresa');
    Route::resource('Faturamentos', App\Http\Controllers\FaturamentosController::class);

    #Sicredi
    Route::get('Sicredi/ConsultaBoleto', App\Http\Livewire\Sicredi\ConsultaBoleto::class);
    Route::resource('DevSicredi', App\Http\Controllers\DevSicrediController::class);
    #Faturamentos Sicredi
    Route::resource('Sicredi', App\Http\Controllers\SicrediController::class);

    #Historicos
    Route::post('Historicos/pesquisapost', [App\Http\Controllers\HistoricoController::class, 'pesquisapost'])->name('pesquisapost');
    Route::resource('Historicos', App\Http\Controllers\HistoricoController::class);

    #Contabilidade
    Route::get('/Contabilidade', function () {
        return view('Contabilidade.dashboard');
    })->name('dashboardContabilidade');

    Route::get('/ContasCarro', function () {
        return view('Contas.carros');
    })->name('ContasCarros');

    #Cobrança
    // Route::resource('Cobranca', App\Http\Controllers\CobrancaController::class);

    Route::get('/Cobranca', function () {
        return view('Cobranca/dashboard');
    });

    #Cadastros
    Route::get('/Cadastros', function () {
        return view('Cadastros/dashboard');
    });

    #OpenAI
    Route::get('/openai', function () {
        return view('openai.index');
    })->name('openai.menu');
    Route::match(['get', 'post'], '/openai/chat', [OpenAIController::class, 'chat'])->name('openai.chat');
    Route::post('/openai/chat/clear', [OpenAIController::class, 'clearChat'])->name('openai.chat.clear');
    Route::get('/openai/chat/new', [OpenAIController::class, 'newChat'])->name('openai.chat.new');
    // Conversas salvas
    Route::get('/openai/chats', [OpenAIController::class, 'chats'])->name('openai.chats');
    Route::post('/openai/chat/save', [OpenAIController::class, 'saveChat'])->name('openai.chat.save');
    Route::get('/openai/chat/load/{chat}', [OpenAIController::class, 'loadChat'])->name('openai.chat.load');
    Route::match(['put','patch'], '/openai/chat/{chat}', [OpenAIController::class, 'updateChat'])->name('openai.chat.update');
    Route::delete('/openai/chat/{chat}', [OpenAIController::class, 'deleteChat'])->name('openai.chat.delete');
    Route::match(['get', 'post'], '/openai/transcribe', [OpenAIController::class, 'transcribe'])->name('openai.transcribe');
});

require __DIR__ . '/auth.php';
