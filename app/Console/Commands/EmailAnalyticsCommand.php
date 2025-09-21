<?php

namespace App\Console\Commands;

use App\Models\FaxJob;
use Illuminate\Console\Command;

class EmailAnalyticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:analytics {--days=30 : Number of days to analyze}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View email click tracking analytics for reminder emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $startDate = now()->subDays($days);

        $this->info("📊 Email Analytics Report - Last {$days} days");
        $this->line("Period: {$startDate->format('M j, Y')} - " . now()->format('M j, Y'));
        $this->newLine();

        // Get all fax jobs with email data
        $faxJobs = FaxJob::where('created_at', '>=', $startDate)
            ->whereIn('status', [FaxJob::STATUS_PENDING, FaxJob::STATUS_PAYMENT_PENDING, FaxJob::STATUS_PAID])
            ->get();

        $totalJobs = $faxJobs->count();
        $earlyRemindersSent = $faxJobs->where('early_reminder_sent', true)->count();
        $remindersSent = $faxJobs->where('reminder_email_sent', true)->count();
        $totalEmailsClicked = $faxJobs->where('email_click_count', '>', 0)->count();
        $totalClicks = $faxJobs->sum('email_click_count');
        $conversions = $faxJobs->where('status', FaxJob::STATUS_PAID)->count();

        // Summary table
        $this->table(
            ['Metric', 'Count', 'Rate'],
            [
                ['Total Fax Jobs', number_format($totalJobs), '-'],
                ['Early Reminders Sent', number_format($earlyRemindersSent), $this->percentage($earlyRemindersSent, $totalJobs)],
                ['Discount Reminders Sent', number_format($remindersSent), $this->percentage($remindersSent, $totalJobs)],
                ['Emails Clicked', number_format($totalEmailsClicked), $this->percentage($totalEmailsClicked, $earlyRemindersSent + $remindersSent)],
                ['Total Clicks', number_format($totalClicks), '-'],
                ['Conversions (Paid)', number_format($conversions), $this->percentage($conversions, $totalJobs)],
                ['Click-to-Conversion Rate', '-', $this->percentage($conversions, $totalEmailsClicked)],
            ]
        );

        // Detailed breakdown
        $this->newLine();
        $this->info("📧 Email Type Breakdown:");

        $earlyClicks = 0;
        $reminderClicks = 0;
        $earlyConversions = 0;
        $reminderConversions = 0;

        foreach ($faxJobs->where('email_click_count', '>', 0) as $faxJob) {
            $stats = $faxJob->getEmailClickStats();
            $earlyClicks += $stats['early_reminder_clicks'];
            $reminderClicks += $stats['reminder_clicks'];
            
            if ($faxJob->status === FaxJob::STATUS_PAID) {
                if ($stats['early_reminder_clicks'] > 0) {
                    $earlyConversions++;
                }
                if ($stats['reminder_clicks'] > 0) {
                    $reminderConversions++;
                }
            }
        }

        $this->table(
            ['Email Type', 'Sent', 'Clicks', 'Click Rate', 'Conversions', 'Conversion Rate'],
            [
                [
                    'Early Reminder (2hr)',
                    number_format($earlyRemindersSent),
                    number_format($earlyClicks),
                    $this->percentage($earlyClicks, $earlyRemindersSent),
                    number_format($earlyConversions),
                    $this->percentage($earlyConversions, $earlyClicks)
                ],
                [
                    'Discount Reminder (24hr)',
                    number_format($remindersSent),
                    number_format($reminderClicks),
                    $this->percentage($reminderClicks, $remindersSent),
                    number_format($reminderConversions),
                    $this->percentage($reminderConversions, $reminderClicks)
                ]
            ]
        );

        // Recent clicks
        $recentClicks = FaxJob::where('created_at', '>=', $startDate)
            ->whereNotNull('email_clicks')
            ->where('email_click_count', '>', 0)
            ->orderBy('reminder_clicked_at', 'desc')
            ->orderBy('early_reminder_clicked_at', 'desc')
            ->take(10)
            ->get();

        if ($recentClicks->count() > 0) {
            $this->newLine();
            $this->info("🔗 Recent Email Clicks:");
            
            $clickData = $recentClicks->map(function ($fax) {
                $stats = $fax->getEmailClickStats();
                $lastClickTime = max($fax->early_reminder_clicked_at, $fax->reminder_clicked_at);
                
                return [
                    "#{$fax->id}",
                    substr($fax->sender_email, 0, 20) . '...',
                    $stats['total_clicks'],
                    $fax->status,
                    $lastClickTime ? $lastClickTime->format('M j, g:i A') : '-'
                ];
            })->toArray();

            $this->table(
                ['Fax ID', 'Email', 'Clicks', 'Status', 'Last Click'],
                $clickData
            );
        }

        $this->newLine();
        $this->comment('💡 Use --days=X to change the analysis period');
        $this->comment('🌐 Full analytics available at: /admin/email-analytics');

        return 0;
    }

    private function percentage($numerator, $denominator)
    {
        if ($denominator == 0) return '0%';
        return number_format(($numerator / $denominator) * 100, 1) . '%';
    }
}