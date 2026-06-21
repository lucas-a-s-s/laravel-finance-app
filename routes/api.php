<?php

use App\Http\Controllers\Api\V1\AccountIndexController;
use App\Http\Controllers\Api\V1\CategoryIndexController;
use App\Http\Controllers\Api\V1\DashboardSummaryController;
use App\Http\Controllers\Api\V1\FinancialTransactionIndexController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')
    ->prefix('v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::get('/dashboard', DashboardSummaryController::class)->name('dashboard');
        Route::get('/accounts', AccountIndexController::class)->name('accounts.index');
        Route::get('/categories', CategoryIndexController::class)->name('categories.index');
        Route::get('/financial-transactions', FinancialTransactionIndexController::class)->name('financial-transactions.index');
    });
