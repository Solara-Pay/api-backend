<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    
        protected $hidden = [
        'secretkey',
        'id'
    ];
    
    protected $fillable = [
        'user_id',
        'email',
        'publickey',
        'secretkey',
        'balance',
        ];
}
