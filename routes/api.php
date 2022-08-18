<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewPeriodController;
use App\Http\Controllers\StandbyController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
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
    Route::post('/logout', [AuthController::class,'logout']);

    Route::post('/users',[UserController::class, 'store']);
    Route::put('/users/{user}',[UserController::class, 'update']);
    Route::delete('/users/{user}',[UserController::class, 'destroy']);

    Route::put('/reviews/{review}',[ReviewController::class, 'update']);
    Route::delete('/reviews/{review}',[ReviewController::class, 'destroy']);
});

Route::controller(UserController::class)->group(function(){
    Route::get('/users', 'index');
    Route::get('/users/{user}', 'show');
    Route::get('/users/{user}/reviews', 'showReviews');
});

Route::controller(AuthController::class)->group(function(){
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

Route::controller(ReviewController::class)->group(function(){
    Route::get('/reviews','index');
    Route::get('/reviews/{review}','show');
    Route::post('/reviews','store');
    Route::get('/reviews/{student_id}/standbys','checkStandby');
    Route::get('/schedule/{startStr}/{endStr}/{user}','schedule');
});

Route::controller(StandbyController::class)->group(function(){
    Route::get('/standbys','index');
    Route::get('/standbys/{standby}','show');
    Route::post('/standbys','store');
    Route::get('/standbys/{standby}/students','checkReceipt');
});

Route::controller(StudentController::class)->group(function(){
    Route::get('/students','index');
    Route::get('/students/{student}','show');
    Route::post('/students','store');
});

Route::controller(ReviewPeriodController::class)->group(function(){
    Route::get('/reviewperiods','index');
    Route::get('/reviewperiods/{reviewperiod}','show');
    Route::post('/reviewperiods','store');
});