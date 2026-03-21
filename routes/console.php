<?php

use App\Console\Commands\DailyInventoryCheck;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\DailyRevenueReportJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(DailyInventoryCheck::class)->dailyAt('08:00');
Schedule::job(new DailyRevenueReportJob)->dailyAt('00:00');