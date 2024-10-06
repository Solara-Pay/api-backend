<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\API\SolController;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\PayrollGroup;
use App\Models\PayrollRecipient;

class AuthController extends Controller
{
    /**
 * @unauthenticated
 */
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed', 'data' => $validator->errors()], 400);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $accessToken =  $user->createToken("API TOKEN", ['login'])->plainTextToken;
            return response()->json(['user' => $user, 'accessToken' => $accessToken], 200);
        } else {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }

    /**
 * @unauthenticated
 */
    public function register(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'username' => 'required|string',
            'country' => 'required|string',
            'wallet_address' => 'sometimes'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed', 'data' => $validator->errors()], 400);
        }

  $wallet = $this->createWallet();
  $secretKeyJson = $wallet['secretKey'];
    // Create the user
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'username' => $request->username,
        'country' => $request->country,
        'wallet_address' => $request->wallet_address ?? 'NULL',
        'pubkey' => $wallet['publicKey'],
        'secretkey' => $wallet['secretKey'],
        'api_key' => getStr()
    ]);

        $accessToken =  $user->createToken("API TOKEN", ['login'])->plainTextToken;
        return response()->json(['user' => $user, 'access_token' => $accessToken], 201);
    }


    public function logout(Request $request){
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    /**
 * @unauthenticated
 */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed', 'data' => $validator->errors()], 400);
        }

        $response = Password::sendResetLink($request->only('email'));
        if ($response == Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent to your email'], 200);
        } else {
            return response()->json(['error' => 'Unable to send reset link'], 400);
        }
    }

    /**
 * @unauthenticated
 */
    public function passwordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed', 'data' => $validator->errors()], 400);
        }

        $response = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            $user->password = bcrypt($password);
            $user->save();
        });

        if ($response == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successful'], 200);
        } else {
            return response()->json(['error' => 'Unable to reset password'], 400);
        }
    }

    public function update(Request $request){
        $validator = $request->validate([
            'name' => 'sometimes',
            'profile_photo' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            'webhook' => 'sometimes'
        ]);

        $user = auth()->user();
        if($request->image){
        $imagePath = $request->file('profile_photo')->store('profile_photo', 'public');
        }
        $user->update([
         'name' => $request->name ?? $user->name,
         //'profile_photo_path' => $imagePath ?? $user->profile_photo_path,
         'webhook' => $request->webhook || $user->webhook
        ]);
        return response()->json([
         'message' => 'Profile updated',
        ]);
    }
    
    public function details(Request $request){
        $user = auth()->user();
        $wallet_address = $user->wallet_address;
        $pubkey = $user->pubkey;
        $walletAddressBalanceData = fetchBalance($wallet_address);
        $pubkeyBalanceData = fetchBalance($pubkey);
        $transactionCount = Transaction::where('user_id', $user->id)->distinct()->count();
        $transactionSum = Transaction::where('user_id', $user->id)->distinct()->sum('amount');
        $customers = Account::where('user_id', $user->id)->distinct()->count('email');
        return response()->json([
        'name' => $user->name,
        'email' => $user->email,
        'username' => $user->username,
        'country' => $user->country,
        'wallet_address_balance' => $walletAddressBalanceData,
        'wallet_address' => $user->wallet_address,
        'onsite_wallet_balance' => $pubkeyBalanceData,
        'onsite_wallet_address' => $user->pubkey,
        'api_key' => $user->api_key,
        'webhook' => $user->webhook,
        'transactioncount' => $transactionCount,
        'transactionsum' => $transactionSum,
        'customers' => $customers,
        'payrollgroup' => PayrollGroup::where('user_id', $user->id)->where('status', 1)->count(),
        'payrollrecipient' => PayrollRecipient::where('user_id', $user->id)->where('status', 1)->count(),
        'payrollsums' => PayrollRecipient::where('user_id', $user->id)->where('status', 1)->sum('amount'),
        ]);
    }
    
    public function regenerateApiKey(Request $request){
        $user = auth()->user();
        $user->update([
            'api_key' => getStr()
            ]);
    return response()->json([
        'message' => 'API KEY REGENERATED'
        ]);        
    }
    private function createWallet() {
    // Define the URL
    $url = env('SOLARA_NEXT_BACKEND') . '/api/keypair';

    // Make the POST request
    $response = Http::get($url);

    // Decode the response
    $data = $response->json();

    // Check if the request was successful
    if ($response->successful()) {
        // Process the response
        $publicKey = $data['publicKey'] ?? 'N/A';
        $secretKeyArray = $data['secretKey'] ?? [];

        // Convert secretKey array to string (if needed)
        $secretKey = implode(',', $secretKeyArray);

        return [
            'publicKey' => $publicKey,
            'secretKey' => $secretKey,
        ];
    } else {
        // Handle errors
        return [
            'error' => $response->status(),
            'message' => $response->body(),
        ];
    }

    }
}
