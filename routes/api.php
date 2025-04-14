<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SupplyController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/signup', [AuthController::class, 'signup']);
Route::post('/auth/login', [AuthController::class, 'login']);
// Routes pour la gestion des approvisionnements 
Route::middleware('auth:sanctum')->group(function  () {
    Route::get('/supplies', [SupplyController::class, 'index']);
    Route::post('/supplies', [SupplyController::class, 'store']);
    Route::get('/supplies/{supply}', [SupplyController::class, 'show']);
    Route::put('/supplies/{supply}', [SupplyController::class, 'update']);
    Route::delete('/supplies/{supply}', [SupplyController::class, 'destroy']);
});   

