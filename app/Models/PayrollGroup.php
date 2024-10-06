<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollGroup extends Model
{
    use HasFactory;
    protected $fillable = ['status', 'name', 'user_id'];
}
