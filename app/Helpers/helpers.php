<?php
use Illuminate\Support\Facades\Http;

if (!function_exists('fetchBalance')) {
    function fetchBalance($publicKey)
    {
        try {
            // Perform the POST request
            $response = Http::post(env('SOLARA_NEXT_BACKEND') . '/api/balance', [
                'publicKey' => $publicKey,
            ]);

            // Check if the request was successful
            if ($response->successful()) {
                $data = $response->json();
                $balanceInSol = $data['balanceInSol'] ?? null;
                return [
                    $balanceInSol,
                ];
            } else {
                return [
                    'error' => 'Failed to fetch balance',
                    'details' => $response->body(),
                ];
            }
        } catch (\Exception $e) {
            return [
                'error' => 'An error occurred',
                'details' => $e->getMessage(),
            ];
        }
    }
    
    if (!function_exists('generateWallet')) {
    function generateWallet(){
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
        $secretKey= $data['secretKey'] ?? [];
        //$secretKeyArray = trim($data['secretKey'], '[]');
        // Convert secretKey array to string (if needed)
        //$secretKey = implode(',', $secretKeyArray);

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
    
        if (!function_exists('fetchTransaction')) {
    function fetchTransaction($publicKey)
{
    // Define the URL
    $url = env('SOLARA_NEXT_BACKEND') . '/api/transaction/latest';

    // Make the POST request
    $response = Http::post($url, [
        'publicKey' => $publicKey
    ]);

    // Decode the response
    $data = $response->json();
\Log::info($data);
    // Check if the request was successful
    if ($response->successful()) {
        // Process the response and ensure proper nesting
        $transactionData = $data['transaction'] ?? null;
        $metaData = $transactionData['meta'] ?? null;

        if ($metaData) {
            return [
                'fee' => $metaData['fee'] ?? 0, // Default to 0 if fee is missing
                'slot' => $transactionData['slot'] ?? null, // Default to null if slot is missing
                'signatures' => $transactionData['transaction']['signatures'][0] ?? [], // Default to empty array if signatures are missing
                'preBalance' => $metaData['preBalances'][0] ?? 0, // Default to 0 if preBalance is missing
                'postBalance' => $metaData['postBalances'][0] ?? 0, // Default to 0 if postBalance is missing
                'details' => $transactionData, // Return the transaction details
                'accountKey' => $transactionData['transaction']['message']['accountKeys'][1]
            ];
        } else {
            return [
                'error' => 'Invalid response structure',
                'message' => 'Missing meta information'
            ];
        }
    } else {
        // Handle errors
        return [
            'error' => $response->status(),
            'message' => $response->body(),
        ];
    }
}

    }
    
}

  function getStr($length = 32)
    {
        $characters = 'ABCDEFGHJKMNOPQRSTUVWXYZ123456789abcdefghijklmnopqrstuvwz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
