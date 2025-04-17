<?php

// Définition du namespace où se trouve le modèle

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Définition de la classe Supply qui étend le modèle Eloquent de base

class Supply extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'supplier_name'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}