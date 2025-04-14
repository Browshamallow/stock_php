<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

   public function index(Request $request)
{
    $search = $request->query('search');

    $products = Product::when($search, function ($query, $search) {
        return $query->where('name', 'like', "%{$search}%");
    })->get();

    return response()->json($products);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'price' => 'required|numeric|min:0',
            'current_stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $validated['image'] = config('app.url') . Storage::url($path);
            }

            $product = Product::create($validated);
                
            return response()->json([
                'message' => 'Produit créé avec succès',
                'data' => $product
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la création',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'sometimes|string|min:2',
                'price' => 'sometimes|numeric|min:0',
                'current_stock' => 'sometimes|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('image')) {
                if ($product->image) {
                    $filePath = parse_url($product->image, PHP_URL_PATH);
                    Storage::disk('public')->delete(str_replace('/storage/', '', $filePath));
                }
                
                $path = $request->file('image')->store('products', 'public');
                $validated['image'] = config('app.url') . Storage::url($path);
            }

            $product->update($validated);

            return response()->json([
                'message' => 'Produit mis à jour avec succès',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            if ($product->image) {
                $filePath = parse_url($product->image, PHP_URL_PATH);
                Storage::disk('public')->delete(str_replace('/storage/', '', $filePath));
            }

            $product->delete();

            return response()->json([
                'message' => 'Produit supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}