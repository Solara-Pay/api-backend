<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'account_id', 'amount', 'balance', 'info', 'slot', 'signatures', 'remark'];
}
