<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

//Schedule::command('app:process-transaction')->everyTenSeconds();
Schedule::command('app:process-payroll-daily')->daily();
Schedule::command('app:process-payroll-weekly')->weekly();
Schedule::command('app:process-payroll-monthly')->monthly();
Schedule::command('app:process-payroll-yearly')->yearly();
