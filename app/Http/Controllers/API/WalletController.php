<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalletController extends Controller
{
        public function details(Request $request){
        $user = auth()->user();
        $wallet_address = $user->wallet_address;
        $pubkey = $user->pubkey;
        $walletAddressBalanceData = fetchBalance($wallet_address);
        $pubkeyBalanceData = fetchBalance($pubkey);
        
           return response()->json([
        'wallet_address_balance' => $walletAddressBalanceData,
        'onsite_wallet_balance' => $pubkeyBalanceData,
        ]);
        }
}
