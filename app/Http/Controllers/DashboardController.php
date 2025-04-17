<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
