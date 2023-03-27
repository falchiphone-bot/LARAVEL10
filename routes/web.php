<?php

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

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('Empresas',App\Http\Controllers\EmpresaController::class);
    Route::resource('Teste',App\Http\Controllers\TesteController::class);
    Route::resource('Usuarios',App\Http\Controllers\UserController::class);

    Route::post('Usuarios/salvarpermissao/{id}',[App\Http\Controllers\UserController::class, 'salvarpermissao']);
    Route::post('Usuarios/salvarfuncao/{id}',[App\Http\Controllers\UserController::class, 'salvarfuncao']);


    Route::resource('Permissoes',App\Http\Controllers\PermissionController::class);
    Route::resource('ModelodeFuncoes',App\Http\Controllers\Model_has_RoleController::class);
    Route::resource('PlanoContas',App\Http\Controllers\PlanoContaController::class);


    Route::resource('Funcoes',App\Http\Controllers\RoleController::class);
    Route::post('Funcoes/salvarpermissao/{id}',[App\Http\Controllers\RoleController::class, 'salvarpermissao']);
});
require __DIR__.'/auth.php';
