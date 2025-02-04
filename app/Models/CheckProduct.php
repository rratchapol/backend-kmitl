<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckProduct extends Model
{
    use HasFactory;

    protected $table = 'checkproducts';
    protected $fillable = ['word'];

}
