<?php



namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Supply;
use App\Models\StockOut;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return response()->json([
            'total_products' => $this->getTotalProducts(),
            'total_supplies' => $this->getTotalSupplies(),
            'total_stock_outs' => $this->getTotalStockOuts(),
            'recent_activities' => $this->getRecentActivities(),
            'stock_evolution' => $this->getStockEvolution(),
            'product_distribution' => $this->getProductDistribution()
        ]);
    }

    private function getTotalProducts()
    {
        return Product::count();
    }

    private function getTotalSupplies()
    {
        return Supply::count();
    }

    private function getTotalStockOuts()
    {
        return StockOut::count();
    }

    private function getRecentActivities()
    {
        return [
            'supplies' => Supply::with('product:id,name')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($supply) {
                    return [
                        'id' => $supply->id,
                        'product_name' => $supply->product->name,
                        'quantity' => $supply->quantity,
                        'created_at' => $supply->created_at
                    ];
                }),
            'stock_outs' => StockOut::with('product:id,name')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($stockOut) {
                    return [
                        'id' => $stockOut->id,
                        'product_name' => $stockOut->product->name,
                        'quantity' => $stockOut->quantity,
                        'created_at' => $stockOut->created_at
                    ];
                })
        ];
    }

    private function getStockEvolution()
    {
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
    
        $supplies = Supply::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(quantity) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'date');
    
        $stockOuts = StockOut::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(quantity) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'date');
        // Calcule le stock initial avant la période
        $initialStock = Supply::where('created_at', '<', $startDate)->sum('quantity')
                      - StockOut::where('created_at', '<', $startDate)->sum('quantity');
    
        $evolution = [];
        $currentStock = $initialStock;
    
        // Génère toutes les dates de la période
        $period = new \DatePeriod(
            $startDate->copy()->subDay(),
            new \DateInterval('P1D'),
            $endDate
        );
    
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $supply = $supplies->get($dateString, 0);
            $stockOut = $stockOuts->get($dateString, 0);
            
            $currentStock += ($supply - $stockOut);
            
            $evolution[] = [
                'date' => $dateString,
                'stock' => $currentStock
            ];
        }
    
        return $evolution;
    }

    private function getProductDistribution()
    {
        return Product::orderByDesc('current_stock')
            ->get(['name', 'current_stock as stock'])
            ->map(function ($product) {
                return [
                    'name' => $product->name,
                    'stock' => $product->stock
                ];
            });
    }
}