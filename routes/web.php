<?php

use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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

Route::resource('teste', App\Http\Controllers\TesteController::class);

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    #Rotas criadas automaticamente laravel
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    #
    Route::get('PlanoContas/pesquisaavancada', [App\Http\Controllers\GoogleCalendarController::class, 'pesquisaavancada'])->name('planocontas.pesquisaavancada');

    #testes
    Route::get('Agenda',[\App\Http\Controllers\TesteController::class,'googleAgenda']);

    #GoogleCalendar

   Route::get('Agenda/dashboard', [App\Http\Controllers\GoogleCalendarController::class, 'dashboard'])->name('google.dashboard');
    Route::resource('Agenda', App\Http\Controllers\GoogleCalendarController::class);
     
    #Empresas

    Route::put('Empresas/desbloquearempresas' ,[App\Http\Controllers\EmpresaController::class, 'desbloquearempresas'])->name('Empresas.DesbloquearEmpresas');
    Route::put('Empresas/bloquearempresas', [App\Http\Controllers\EmpresaController::class, 'bloquearempresas'])->name('Empresas.BloquearEmpresas');
    Route::put('Empresas/desbloquearempresas', [App\Http\Controllers\EmpresaController::class, 'desbloquearempresas'])->name('Empresas.DesbloquearEmpresas');
    Route::resource('Empresas', App\Http\Controllers\EmpresaController::class);

    Route::resource('Teste', App\Http\Controllers\TesteController::class);

    #Gerenciamento de Usuários
    Route::resource('Usuarios', App\Http\Controllers\UserController::class);
    Route::post('Usuarios/salvarpermissao/{id}', [App\Http\Controllers\UserController::class, 'salvarpermissao']);
    Route::post('Usuarios/salvarfuncao/{id}', [App\Http\Controllers\UserController::class, 'salvarfuncao']);
    Route::post('Usuarios/salvar-empresa/{id}', [App\Http\Controllers\UserController::class, 'salvarEmpresa']);

    #Gerenciamento de permissões e funções
    Route::resource('Permissoes', App\Http\Controllers\PermissionController::class);
    Route::resource('ModelodeFuncoes', App\Http\Controllers\Model_has_RoleController::class);

    #PlanoContas
    Route::get('PlanoContas/pesquisaavancada', [App\Http\Controllers\PlanoContaController::class, 'pesquisaavancada'])->name('planocontas.pesquisaavancada');
    Route::post('PlanoContas/pesquisaavancada', [App\Http\Controllers\PlanoContaController::class, 'pesquisaavancadapost'])->name('planocontas.pesquisaavancada.post');
    Route::get('PlanoContas/autenticar/{EmpresaID}', [App\Http\Controllers\EmpresaController::class, 'autenticar'])->name('planocontas.autenticar');
    Route::get('PlanoContas/dashboard', [App\Http\Controllers\PlanoContaController::class, 'dashboard'])->name('planocontas.dashboard');
    Route::resource('PlanoContas', App\Http\Controllers\PlanoContaController::class);

    #Contas
    Route::get('Contas/Extrato/{contaID}', [App\Http\Controllers\ContaController::class, 'extrato']);
    Route::resource('Contas', App\Http\Controllers\ContaController::class);
    Route::resource('ContasCobranca', App\Http\Controllers\ContaCobrancaController::class);

    #Funções
    Route::resource('Funcoes', App\Http\Controllers\RoleController::class);
    Route::post('Funcoes/salvarpermissao/{id}', [App\Http\Controllers\RoleController::class, 'salvarpermissao']);

    #Moedas e valores
    Route::get('Moedas/dashboard', [App\Http\Controllers\MoedaController::class, 'dashboard'])->name('moedas.dashboard');
    Route::resource('Moedas', App\Http\Controllers\MoedaController::class);

    Route::resource('MoedasValores', App\Http\Controllers\MoedaValoresController::class);

    #Faturamentos
    Route::resource('Faturamentos', App\Http\Controllers\FaturamentosController::class);

    #Faturamentos Sicredi
    Route::resource('Sicredi', App\Http\Controllers\SicrediController::class);

    #Sicredi
    Route::resource('DevSicredi', App\Http\Controllers\DevSicrediController::class);

    #Historicos
    Route::post('Historicos/pesquisapost', [App\Http\Controllers\HistoricoController::class, 'pesquisapost'])->name('pesquisapost');
    Route::resource('Historicos', App\Http\Controllers\HistoricoController::class);

    #Contabilidade
    Route::get('/Contabilidade', function () {
        return view('Contabilidade/dashboard');
    })
        ->middleware(['auth', 'verified'])
        ->name('dashboardContabilidade');

    #Cobrança
    Route::resource('Cobranca', App\Http\Controllers\CobrancaController::class);

    Route::get('/Cobranca', function () {
        return view('Cobranca/dashboard');
    });
});
require __DIR__ . '/auth.php';
