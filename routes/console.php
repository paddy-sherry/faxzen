<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule fax status checking every 2 minutes
app(Schedule::class)->command('fax:check-status')->everyTwoMinutes();

// Schedule early fax reminder emails (2 hours) to be sent every 2 hours
app(Schedule::class)->command('fax:send-reminders --hours=2')->everyTwoHours();

// Schedule discount reminder emails (24+ hours) to be sent twice daily
app(Schedule::class)->command('fax:send-reminders --hours=24')->dailyAt('13:00');
app(Schedule::class)->command('fax:send-reminders --hours=24')->dailyAt('01:00');

// Schedule fax document cleanup daily at 2:00 AM (48 hours after creation)
app(Schedule::class)->command('fax:cleanup-documents')->dailyAt('02:00');

// Schedule expired token cleanup daily at 3:00 AM for security
app(Schedule::class)->command('tokens:cleanup')->dailyAt('03:00');
