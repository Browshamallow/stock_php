<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'reason',
        'date'
    ];

    protected $attributes = [
        'date' => null
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}