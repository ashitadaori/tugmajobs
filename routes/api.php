<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('locations')->group(function () {
    Route::get('/areas', [App\Http\Controllers\Api\LocationController::class, 'getAllAreas']);
    Route::get('/search', [App\Http\Controllers\Api\LocationController::class, 'search']);
    Route::get('/nearest', [App\Http\Controllers\Api\LocationController::class, 'getNearestArea']);
});
