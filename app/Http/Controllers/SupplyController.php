<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplyController extends Controller
{
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
            $validated = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'supplier_name' => 'required|string|max:255'
            ]);

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
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
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
            $validated = $request->validate([
                'product_id' => 'sometimes|integer|exists:products,id',
                'quantity' => 'sometimes|integer|min:1',
                'supplier_name' => 'sometimes|string|max:255'
            ]);

            return DB::transaction(function () use ($id, $validated) {
                $supply = Supply::findOrFail($id);
                $originalQuantity = $supply->quantity;

                // Mise à jour des données
                $supply->update($validated);

                // Si la quantité a changé
                if (isset($validated['quantity'])) {
                    $product = Product::findOrFail($supply->product_id);
                    $quantityDifference = $validated['quantity'] - $originalQuantity;
                    $product->current_stock += $quantityDifference;
                    $product->save();
                }

                return response()->json($supply);
            });

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
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
            return DB::transaction(function () use ($id) {
                $supply = Supply::findOrFail($id);
                $product = Product::findOrFail($supply->product_id);
                
                // Réduction du stock
                $product->current_stock -= $supply->quantity;
                $product->save();

                // Suppression de l'approvisionnement
                $supply->delete();

                return response()->json([
                    'message' => 'Approvisionnement supprimé avec succès'
                ]);
            });

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur du serveur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}