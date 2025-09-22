<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



route::post('/whatsapp', [ApiController::class,'index']);
route::get('/whatsapp', [ApiController::class,'index']);

// Market status público (sem sessão), com throttle e simple.limit
Route::middleware(['throttle:120,1'])->group(function() {
    Route::get('/market/status', [\App\Http\Controllers\MarketDataController::class, 'status'])
        ->middleware('simple.limit:status,20,10')
        ->name('api.market.status');
});

