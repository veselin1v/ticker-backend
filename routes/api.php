<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\TickerController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\DividendController;
use App\Http\Controllers\TradeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function() {
    Route::get('get/user', function (Request $request) {
        return $request->user();
    });
    Route::resource('portfolios', PortfolioController::class);
    Route::get('check/auth', fn() => true );
    Route::get('tickers/search/{name}', [TickerController::class, 'search']);
    Route::resource('assets', AssetController::class);
    Route::get('dividends', [DividendController::class, 'index']);
    Route::post('trades', [TradeController::class, 'store'])->middleware(['check.quantity']);
});
