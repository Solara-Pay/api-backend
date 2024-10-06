<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessPayrollMonthly as Monthly;

class ProcessPayrollMonthly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-payroll-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes Payroll which are to be sent out monthly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Monthly::dispatch();
        $this->info('Monthly Payroll has been dispatched');
    }
}
