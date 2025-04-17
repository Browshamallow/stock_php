<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats()
    {
        $totalUsers = User::count();
        $totalProducts = Product::count();

        return response()->json([
            'total_users' => $totalUsers,
            'total_products' => $totalProducts,
            'message' => 'Statistiques récupérées avec succès'
        ]);
    }
}
