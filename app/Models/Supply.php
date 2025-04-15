<?php

// Définition du namespace où se trouve le modèle

namespace App\Models;

// Importation des classes nécessaires

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Définition de la classe Supply qui étend le modèle Eloquent de base

class Supply extends Model
{
     // Utilisation du trait HasFactory pour permettre la création de factories

    use HasFactory;

    // Définition des champs qui peuvent être remplis massivement

    protected $fillable = [
        'product_id',   // ID du produit associé
        'quantity',     // Quantité approvisionnée
        'supplier_name'   // Nom du fournisseur
    ];

     // Définition de la relation "appartient à" avec le modèle Product

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
