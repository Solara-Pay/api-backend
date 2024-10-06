<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AccountController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\PayrollController;
use App\Http\Controllers\CronController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('run/cron', [CronController::class, 'runScheduler']);
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('passowrd/request', [AuthController::class, 'sendResetLinkEmail']);
Route::post('passowrd/reset', [AuthController::class, 'passwordReset']);

//Protected via Custom API Key
Route::post('accounts/generate', [AccountController::class, 'generate']);
//Protected Routes
Route::middleware(['auth:sanctum'])->group(function () {
    //Auth
    Route::post('update-profile', [AuthController::class, 'update']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('regenerate/api-key', [AuthController::class, 'regenerateApiKey']);
    Route::get('details', [AuthController::class, 'details']);
    
    Route::get('accounts/all', [AccountController::class, 'index']);
    
    Route::get('transactions', [TransactionController::class, 'get']);
    Route::get('wallet', [WalletController::class, 'details']);
    
    //Payroll Route Groups
    Route::prefix('payroll')->group(function () {
    Route::post('destroy/group', [PayrollController::class, 'destroyGroup']);
    Route::post('update/group', [PayrollController::class, 'updateGroup']);    
    Route::post('create/group', [PayrollController::class, 'createGroup']);    
    Route::get('groups', [PayrollController::class, 'groups']);
    Route::get('query/group', [PayrollController::class, 'getGroup']);
    Route::post('create/recipient', [PayrollController::class, 'recipient']);
    Route::post('destroy/recipient', [PayrollController::class, 'destroyRecipient']); 
    });
});    