<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessPayrollYearly as Yearly;

class ProcessPayrollYearly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-payroll-yearly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes Payroll which are to be sent out yearly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Yearly::dispatch();
        $this->info('Yearly has been dispatched');
    }
}
