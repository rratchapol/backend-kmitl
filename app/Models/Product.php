<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name', 
        'product_images', 
        'product_qty', 
        'product_price', 
        'product_description', 
        'product_category', 
        'product_type', 
        'seller_id', 
        'date_exp', 
        'location', 
        'condition'
    ];

    protected $casts = [
        'product_images' => 'array', // เก็บรูปเป็น array
    ];
}
