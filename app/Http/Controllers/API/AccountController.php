<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\User;

class AccountController extends Controller
{
    public function index(){
        $accounts = Account::where('user_id', auth()->user()->id)->get();
        return response()->json($accounts);
    }
    
    /**
    * @unauthenticated
    */
    public function generate(Request $request){
        //$request->validate([
        //'email' => 'required|email',
        //'api_key' => 'required|exists:users,api_key'
         //]);
         
        $user = User::where('api_key', $request->api_key)->first();
        if(!$user){
            return response()->json(['message' => 'Kindly pass a correct API KEY in your Payload'], 401);
        }
        if(!$request->email){
            return response()->json(['message' => 'Kindly pass a Valid Email Address'], 400);
        }
        $wallet = generateWallet();
        $acc = Account::create([
            'user_id' => $user->id,
            'email' => $request->email,
            'publickey' => $wallet['publicKey'],
            'secretkey' => json_encode($wallet['secretKey']),
            'balance' => 0
            ]);
    return response()->json([
        'message' => 'Wallet Address Created',
        'publicKey' => $acc->publickey,
        ]);        
    }
}
