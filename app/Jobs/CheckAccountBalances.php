<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;

class CheckAccountBalances implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        // You can pass any parameters to the constructor if needed
    }

    public function handle()
    {
        // Step 1: Query the database to get all accounts' public keys
        $accounts = Account::all(); // Adjust to get the relevant accounts

        foreach ($accounts as $account) {
            $publicKey = $account->publickey;
            $user = User::where('id', $account->user_id)->first();
            $webhook = $user->webhook;

            // Step 2: Fetch balance details using the fetchBalance method
            $balanceResponse = fetchBalance($publicKey);

            if ($balanceResponse) {
                $currentBalance = $balanceResponse ?? 0;

                // Step 3: Fetch transaction details using the fetchTransaction method
                $transactionResponse = fetchTransaction($publicKey);
                          
                if ($transactionResponse) {
                    \Log::info($transactionResponse);
        $fee = $transactionResponse['fee']; // Default to 0 if fee is missing
        $slot = $transactionResponse['slot'] ?? null; // Default to null if slot is missing
        $signatures = $transactionResponse['signatures'] ?? []; // Default to an empty array
        $preBalance = $transactionResponse['preBalance'] ?? 0; // Default to 0 if missing
        $postBalance = $transactionResponse['postBalance'] ?? 0;
        $accountKey = $transactionResponse['accountKey'];
                // Log or store the data as needed
        \Log::info("Fee: $fee, Slot: $slot, Signature: " .$signatures);
        \Log::info("Pre Balance: $preBalance, Post Balance: $postBalance");
                    
                    $amount = $preBalance - $postBalance;
                    $actualAmount = ($amount - $fee) / 1000000000;
                    // Step 4: Check if the retrieve transaction signature exist in the database, if it does it stops the job else proceed
                    $lastTransaction = Transaction::where('signature', $signatures)
                        ->first();
                    if ($lastTransaction) {
                        return;
                    }     
                if($accountKey == $publicKey) {
                     $info = json_encode([
                      'fee' => $fee,
                     'slot' => $slot,
                     'signatures' => $signatures
                    ], JSON_PRETTY_PRINT); // Optional: This makes the JSON easier to read

                    if (!$lastTransaction) {
                        // Step 5: Create a new transaction record
                       $trx = Transaction::create([
                            'user_id' => $account->user_id, 
                            'account_id' => $account->id,
                            'amount' => $actualAmount,
                            'info' => $info,
                            'signature' => $signatures,                            
                            'remark' => 'CONFIRMED',
                        ]);
                        $account->update([
                            'balance' => $currentBalance[0],
                        ]);
                        //\Log::info($account);
                        // Step 6: Send a webhook notification
                        $this->sendWebhookNotification($account, $actualAmount, $webhook, $currentBalance, $postBalance, $fee, $slot, $signatures);
                    }
                } else {
                    // Handle error response for fetchTransaction
                    \Log::error('Failed to fetch transaction details for publicKey: ' . $publicKey);
                }
                }       

                   
            } else {
                // Handle error response for fetchBalance
                \Log::error('Failed to fetch balance for publicKey: ' . $publicKey);
            }
        }
        
    }




    private function sendWebhookNotification($account, $actualAmount, $webhook, $currentBalance, $postBalance, $fee, $slot, $signatures)
    {
        $webhookUrl = $webhook; // URL for Webhook to be sent to Merchant

        $payload = [
            'email' => $account->email,
            'amount' => $actualAmount,
            'publicKey' => $account->publickey,
            'currentBalance' => $currentBalance[0],
            //'lastBalance' => $postBalance,
            'fee' => $fee,
            'slot' => $slot,
            'signature' => $signatures,
        ];

\Log::info($payload);
        try {
            $response = Http::post($webhookUrl, $payload);

            if ($response->failed()) {
                \Log::error('Failed to send webhook notification for account_id: ' . $account->id);
            }
        } catch (\Exception $e) {
            \Log::error('Exception sending webhook notification for account_id: ' . $account->id . ' - ' . $e->getMessage());
        }
    }
}
