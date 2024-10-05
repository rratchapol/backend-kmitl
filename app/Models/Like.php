<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'userlike_id',
        'product_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userlike_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
