<?php

// Définition du namespace pour le contrôleur
namespace App\Http\Controllers;

// Importation des classes nécessaires
use App\Models\Supply;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplyController extends Controller
{
    // Constructeur avec middleware d'authentification
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Affiche la liste des approvisionnements
     */
    public function index()
    {
        return Supply::with('product:id,name')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Crée un nouvel approvisionnement
     */
    public function store(Request $request)
    {
        try {
            // Validation des données entrantes
            $validated = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'supplier_name' => 'required|string|max:255'
            ]);

            // Transaction pour assurer l'intégrité des données
            return DB::transaction(function () use ($validated) {
                // Création de l'approvisionnement
                $supply = Supply::create($validated);

                // Mise à jour du stock du produit
                $product = Product::findOrFail($validated['product_id']);
                $product->current_stock += $validated['quantity'];
                $product->save();

                return response()->json($supply, 201);
            });

        } catch (ValidationException $e) {
            // Gestion des erreurs de validation
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Gestion des autres erreurs
            return response()->json([
                'message' => 'Erreur du serveur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche un approvisionnement spécifique
     */
    public function show($id)
    {
        try {
            return Supply::with('product:id,name')->findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Approvisionnement non trouvé'
            ], 404);
        }
    }

    /**
     * Met à jour un approvisionnement
     */
    public function update(Request $request, $id)
    {
        try {
            // Validation des données entrantes
            $validated = $request->validate([
                'product_id' => 'sometimes|integer|exists:products,id',
                'quantity' => 'sometimes|integer|min:1',
                'supplier_name' => 'sometimes|string|max:255'
            ]);

            // Transaction pour assurer l'intégrité des données
            return DB::transaction(function () use ($id, $validated) {
                $supply = Supply::findOrFail($id);
                $originalQuantity = $supply->quantity;

                // Mise à jour des données
                $supply->update($validated);

                // Si la quantité a changé, mise à jour du stock
                if (isset($validated['quantity'])) {
                    $product = Product::findOrFail($supply->product_id);
                    $quantityDifference = $validated['quantity'] - $originalQuantity;
                    $product->current_stock += $quantityDifference;
                    $product->save();
                }

                return response()->json($supply);
            });

        } catch (ValidationException $e) {
            // Gestion des erreurs de validation
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Gestion des autres erreurs
            return response()->json([
                'message' => 'Erreur du serveur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime un approvisionnement
     */
    public function destroy($id)
    {
        try {
            // Transaction pour assurer l'intégrité des données
            return DB::transaction(function () use ($id) {
                $supply = Supply::findOrFail($id);
                $product = Product::findOrFail($supply->product_id);
                
                // Réduction du stock avant suppression
                $product->current_stock -= $supply->quantity;
                $product->save();

                // Suppression de l'approvisionnement
                $supply->delete();

                return response()->json([
                    'message' => 'Approvisionnement supprimé avec succès'
                ]);
            });

        } catch (\Exception $e) {
            // Gestion des erreurs
            return response()->json([
                'message' => 'Erreur du serveur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}