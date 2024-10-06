<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class SolController extends Controller
{
    public function fetchKeypair()
{
    // Define the URL
    $url = 'https://solara-next-backend.vercel.app/api/keypair';

    // Make the POST request
    $response = Http::post($url);

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
