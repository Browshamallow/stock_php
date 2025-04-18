<?php
// Importation des classes nécessaires
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockOutController;

// Route protégée pour récupérer les informations de l'utilisateur connecté
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes d'authentification publiques
Route::post('/auth/signup', [AuthController::class, 'signup']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    // Seules les routes protégées
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);


    Route::get('/supplies', [SupplyController::class, 'index']);
    Route::post('/supplies', [SupplyController::class, 'store']);
    Route::get('/supplies/{supply}', [SupplyController::class, 'show']);
    Route::put('/supplies/{supply}', [SupplyController::class, 'update']);
    Route::delete('/supplies/{supply}', [SupplyController::class, 'destroy']);


    Route::apiResource('stock-outs', StockOutController::class)
    ->except(['edit', 'create']);

    // routes/api.php
    Route::get('/dashboard/stats', [StatsController::class, 'index']);
});



