<?php


namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockOut;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockOutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return StockOut::with('product:id,name')
            ->orderByDesc('created_at')
            ->get();
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'reason' => 'required|string|max:255'
            ]);

            return DB::transaction(function () use ($validated) {
                $product = Product::findOrFail($validated['product_id']);
                
                if ($product->current_stock < $validated['quantity']) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Stock insuffisant'
                    ]);
                }

                // Ajout de la date automatique
                $validated['date'] = now()->toDateString();

                $stockOut = StockOut::create($validated);
                $product->current_stock -= $validated['quantity'];
                $product->save();

                return response()->json($stockOut, 201);
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

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'sometimes|exists:products,id',
                'quantity' => 'sometimes|integer|min:1',
                'reason' => 'sometimes|string|max:255'
            ]);

            return DB::transaction(function () use ($id, $validated) {
                $stockOut = StockOut::findOrFail($id);
                $originalQuantity = $stockOut->quantity;

                if (isset($validated['quantity'])) {
                    $product = Product::findOrFail($stockOut->product_id);
                    $difference = $validated['quantity'] - $originalQuantity;
                    
                    if ($product->current_stock + $originalQuantity < $validated['quantity']) {
                        throw ValidationException::withMessages([
                            'quantity' => 'Stock insuffisant pour cette modification'
                        ]);
                    }

                    $product->current_stock -= $difference;
                    $product->save();
                }

                $stockOut->update($validated);
                return response()->json($stockOut);
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

    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $stockOut = StockOut::findOrFail($id);
                $product = Product::findOrFail($stockOut->product_id);
                
                $product->current_stock += $stockOut->quantity;
                $product->save();
                
                $stockOut->delete();

                return response()->json([
                    'message' => 'Sortie de stock supprimée avec succès'
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