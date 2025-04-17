<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Inscription
    public function signup(Request $request)
    {
        $validateData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validateData['name'],
            'email' => $validateData['email'],
            'password' => bcrypt($validateData['password']),
        ]);

        Auth::login($user); // Login automatique après inscription

        return response()->json([
            'message' => 'Inscription réussie',
            'user' => $user,
        ]);
    }

    public function login(Request $request)
    {
        $validateData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
    
        // Vérifie les identifiants
        if (!Auth::attempt($validateData)) {
            return response()->json([
                'message' => 'Identifiants invalides',
            ], 401);
        }
    
        $user = Auth::user();
    
        // Génère un token pour l'authentification API
        $token = $user->createToken('authToken')->plainTextToken;
    
        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token, // le frontend doit le stocker
        ], 200);
    }
    

    // Déconnexion
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        return response()->json([
            'message' => 'Déconnecté avec succès',
        ]);
    }
}
