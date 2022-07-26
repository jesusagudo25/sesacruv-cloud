<?php

use App\Http\Controllers\AnalystController;
use App\Http\Controllers\ReviewController;
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

Route::middleware('auth:sanctum')->group(function(){
    //Route::post('/logout', [AuthController::class,'logout']);

    Route::post('/analysts',[AnalystController::class, 'store']);
    Route::put('/analysts/{analyst}',[AnalystController::class, 'update']);
    Route::delete('/analysts/{analyst}',[AnalystController::class, 'destroy']);

    Route::put('/reviews/{review}',[ReviewController::class, 'update']);
    Route::delete('/reviews/{review}',[ReviewController::class, 'destroy']);
});


Route::controller(ReviewController::class)->group(function(){
    Route::get('/reviews','index');
    Route::get('/reviews/{review}','show');
    Route::post('/reviews','store');
});