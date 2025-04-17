<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // Applique le middleware d'authentification Sanctum à toutes les méthodes
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Affiche la liste des produits, avec option de recherche par nom.
     */
    public function index(Request $request)
    {
        // Récupère le mot-clé de recherche depuis les paramètres de la requête
        $search = $request->query('search');

        // Si un mot-clé est fourni, filtre les produits dont le nom contient ce mot
        $products = Product::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%");
        })->get();

        // Retourne la liste des produits en format JSON
        return response()->json($products);
    }

    /**
     * Crée un nouveau produit après validation des données.
     */
    public function store(Request $request)
    {
        // Valide les données envoyées dans la requête
        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'price' => 'required|numeric|min:0',
            'current_stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Si une image est fournie, la stocker et sauvegarder son URL
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $validated['image'] = config('app.url') . Storage::url($path);
            }

            // Crée le produit dans la base de données
            $product = Product::create($validated);
                
            // Retourne une réponse JSON de succès
            return response()->json([
                'message' => 'Produit créé avec succès',
                'data' => $product
            ], 201);

        } catch (\Exception $e) {
            // En cas d'erreur, retourne un message d'erreur JSON
            return response()->json([
                'error' => 'Erreur lors de la création',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour un produit existant selon les données fournies.
     */
    public function update(Request $request, $id)
    {
        try {
            // Recherche le produit à mettre à jour, ou échoue s’il n’existe pas
            $product = Product::findOrFail($id);
            
            // Valide uniquement les champs présents dans la requête
            $validated = $request->validate([
                'name' => 'sometimes|string|min:2',
                'price' => 'sometimes|numeric|min:0',
                'current_stock' => 'sometimes|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Si une nouvelle image est envoyée
            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image si elle existe
                if ($product->image) {
                    $filePath = parse_url($product->image, PHP_URL_PATH);
                    Storage::disk('public')->delete(str_replace('/storage/', '', $filePath));
                }

                // Stocker la nouvelle image et mettre à jour son URL
                $path = $request->file('image')->store('products', 'public');
                $validated['image'] = config('app.url') . Storage::url($path);
            }

            // Met à jour le produit avec les nouvelles données
            $product->update($validated);

            // Retourne une réponse JSON de succès
            return response()->json([
                'message' => 'Produit mis à jour avec succès',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            // Retourne une erreur en cas d'exception
            return response()->json([
                'error' => 'Erreur lors de la mise à jour',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime un produit et son image associée si elle existe.
     */
    public function destroy($id)
    {
        try {
            // Recherche du produit à supprimer
            $product = Product::findOrFail($id);
            
            // Supprime l'image du produit du stockage s’il y en a une
            if ($product->image) {
                $filePath = parse_url($product->image, PHP_URL_PATH);
                Storage::disk('public')->delete(str_replace('/storage/', '', $filePath));
            }

            // Supprime le produit de la base de données
            $product->delete();

            // Retourne un message de confirmation
            return response()->json([
                'message' => 'Produit supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            // Retourne un message d'erreur si la suppression échoue
            return response()->json([
                'error' => 'Erreur lors de la suppression',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
