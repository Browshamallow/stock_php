<?php
// Importation des classes nécessaires
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SupplyController;

// Route protégée pour récupérer les informations de l'utilisateur connecté
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes d'authentification publiques
Route::post('/auth/signup', [AuthController::class, 'signup']);
Route::post('/auth/login', [AuthController::class, 'login']);
 
// Routes pour la gestion des approvisionnements (protégées par auth:sanctum)
Route::middleware('auth:sanctum')->group(function  () {
    // GET /api/supplies - Liste tous les approvisionnements
    Route::get('/supplies', [SupplyController::class, 'index']);
    // POST /api/supplies - Crée un nouvel approvisionnement
    Route::post('/supplies', [SupplyController::class, 'store']);
    // GET /api/supplies/{supply} - Affiche un approvisionnement spécifique
    Route::get('/supplies/{supply}', [SupplyController::class, 'show']);
    // PUT /api/supplies/{supply} - Met à jour un approvisionnement
    Route::put('/supplies/{supply}', [SupplyController::class, 'update']);
    // DELETE /api/supplies/{supply} - Supprime un approvisionnement
    Route::delete('/supplies/{supply}', [SupplyController::class, 'destroy']);
});   

