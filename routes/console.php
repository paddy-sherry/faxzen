<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule fax status checking every 2 minutes
app(Schedule::class)->command('fax:check-status')->everyTwoMinutes();

// Schedule fax reminder emails to be sent twice daily at 1:00 PM and 1:00 AM
app(Schedule::class)->command('fax:send-reminders')->dailyAt('13:00');
app(Schedule::class)->command('fax:send-reminders')->dailyAt('01:00');

// Schedule fax document cleanup daily at 2:00 AM (48 hours after creation)
app(Schedule::class)->command('fax:cleanup-documents')->dailyAt('02:00');
