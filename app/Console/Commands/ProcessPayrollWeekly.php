<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessPayrollWeekly as Weekly;

class ProcessPayrollWeekly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-payroll-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes Payroll which are to be sent out weekly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Weekly::dispatch();
        $this->info('Weekly Payroll has been dispatched');
    }
}
