<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollRecipient extends Model
{
    use HasFactory;
    protected $fillable =[
    'payroll_group_id',
    'user_id',
    'name',
    'wallet_address',
    'amount',
    'sol',
    'schedule',
    'status'
    ];
}
