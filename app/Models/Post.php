<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'userpost_id',
        'image',
        'detail',
        'category',
        'tag',
        'price',
        'status'
    ];

    public function user()
    {
        // return $this->belongsTo(User::class, 'userpost_id');
        return $this->belongsTo(Customer::class, 'userpost_id');

    }
}
