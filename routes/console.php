<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule fax status checking every 2 minutes
app(Schedule::class)->command('fax:check-status')->everyTwoMinutes();

// Schedule fax reminder emails to be sent daily at 10 AM
app(Schedule::class)->command('fax:send-reminders')->dailyAt('15:00');
