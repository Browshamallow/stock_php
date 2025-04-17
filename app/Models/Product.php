<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'current_stock', 'image'];
    
    protected function createdAt() : Attribute{
        return Attribute::make(
            get : fn ($value) => Carbon::parse($value)->diffForHumans(),

        );
    }
}
