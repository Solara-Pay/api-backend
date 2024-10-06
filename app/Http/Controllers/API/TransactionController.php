<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function get(Request $request){
        $trx = Transaction::where('user_id', auth()->user()->id)->orderBy('id', 'desc')->paginate(50);
        return response()->json($trx);
    }
}
