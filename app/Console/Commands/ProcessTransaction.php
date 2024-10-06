<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CheckAccountBalances;

class ProcessTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Query Transaction on Wallets and handle well';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Dispatch the CheckAccountBalances job
        CheckAccountBalances::dispatch();
        
        $this->info('Account balance check job dispatched successfully.');
    }
}
