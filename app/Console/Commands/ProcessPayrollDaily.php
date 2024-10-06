<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessPayrollDaily as Daily;

class ProcessPayrollDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-payroll-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes Payroll which are to be sent out daily';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Daily::dispatch();
        $this->info('Daily Payroll has been dispatched');
    }
}
