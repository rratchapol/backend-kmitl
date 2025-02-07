<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'message',
        'image',
        'statusread',
    ];

    public function receiver()
    {
        return $this->belongsTo(Customer::class, 'seller_id');
    }

    public function user()
{
    return $this->belongsTo(Customer::class, 'buyer_id');
}
}
