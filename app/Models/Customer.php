<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'pic',
        'email',
        'mobile',
        'address',
        'faculty',
        'department',
        'classyear',
        'role',
        'user_id',
        'guidetag',
        // 'userhistory',
        // 'userpost',
        // 'userproduct',
        // 'status'
    ];

        // ความสัมพันธ์กับ User
        public function user()
        {
            return $this->belongsTo(User::class);
            // return $this->belongsTo(Customer::class, 'userpost_id');
        }
}
