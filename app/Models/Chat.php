<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'image',
        'statusread',
    ];

    public function receiver()
    {
        return $this->belongsTo(Customer::class, 'receiver_id');
    }

    public function user()
{
    return $this->belongsTo(Customer::class, 'sender_id');
}
}
