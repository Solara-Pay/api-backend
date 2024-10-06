<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PayrollGroup;
use App\Models\PayrollRecipient;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;

class ProcessPayrollYearly implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::all();
        foreach ($users as $user) {
            $groups = PayrollGroup::where('user_id', $user->id)->where('status', 1)->get();
            foreach ($groups as $group) {
                $recipients = PayrollRecipient::where('user_id', $user->id)
                    ->where('payroll_group_id', $group->id)
                    ->where('status', 1)
                    ->where('schedule', 'yearly')
                    ->get();
                
                foreach ($recipients as $recipient) {
                $secretkey = $user->secretkey;  // The series of numbers stored as a string (comma-separated)

                // Convert the comma-separated string into an array
                $secretkeyArray = explode(',', $secretkey);

                // Ensure that all elements in the array are properly trimmed (in case of extra spaces)
                $secretkeyArray = array_map('trim', $secretkeyArray);

                // Format the array back into a comma-separated string wrapped in square brackets
               $formattedSecretKey = "[" . implode(',', $secretkeyArray) . "]";

               // Assign the formatted secret key to be used in the API request
               $secretKey = $formattedSecretKey;
                    try {
                        // Perform the POST request
                        $response = Http::post(env('SOLARA_NEXT_BACKEND') . '/api/transaction/new', [
                            'secretKey' => $secretkey,
                            'toPubKey' => $recipient->wallet_address,
                            'lamports' => $recipient->amount * 1000000000,
                        ]);

                        // Check if the request was successful
                        if ($response->successful()) {
                            $data = $response->json();
                            
                            \Log::info($data);

                            // Update recipient status
                            $recipient->update(['status' => 0]);

                            // Get the balance in SOL from response
                            $balanceInSol = $data['balanceInSol'] ?? null;

                            // Log or process balance
                            \Log::info('Transaction successful. Balance in SOL: ' . $balanceInSol);

                             $info = json_encode([
                              'fee' => 0,
                              'slot' => 0,
                              'signatures' => $data['signature']
                             ], JSON_PRETTY_PRINT); 
                            // Uncomment and complete transaction creation if needed
                             Transaction::create([
                                 'user_id' => $user->id,
                                 'account_id' => 0,
                                  'info' => $info,    
                                 'amount' => $recipient->amount,
                                 'signature' => $data['signature'],
                                 'remark' => "Successfully disbursed payroll to $recipient->name for the $recipient->schedule period. Payroll Group: $group->name",
                             ]);

                        } else {
                            \Log::error('Failed to process transaction', [
                                'user_id' => $user->id,
                                'recipient_id' => $recipient->id,
                                'response' => $response->body(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error processing recipient', [
                            'user_id' => $user->id,
                            'recipient_id' => $recipient->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }
    }
}
