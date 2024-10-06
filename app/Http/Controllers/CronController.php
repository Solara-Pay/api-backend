<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CronController extends Controller
{
     public function runScheduler()
    {
        // Run the scheduler
        $output = [];
        $resultCode = Artisan::call('schedule:run');
        Artisan::call('queue:work');
        // Log the output (optional)
        \Log::info('Scheduler run manually. Output: ' . implode("\n", $output));

        // Check the result
        if ($resultCode === 0) {
            return response()->json(['message' => 'Scheduler ran successfully', 'output' => $output]);
        } else {
            return response()->json(['message' => 'Scheduler encountered an error', 'output' => $output], 500);
        }
    }
}
