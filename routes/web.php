<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/fetch-transaction/{publicKey}', function ($publicKey) {
    // Call the fetchTransaction function
    $response = fetchTransaction($publicKey);
 
    // Use dd() to dump and die the response
    dd($response, $response['slot']);
});
