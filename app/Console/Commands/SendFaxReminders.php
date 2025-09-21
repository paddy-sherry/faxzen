<?php

namespace App\Console\Commands;

use App\Models\FaxJob;
use App\Mail\FaxReminderEmail;
use App\Mail\EarlyFaxReminderEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendFaxReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fax:send-reminders {--hours=12 : Send reminders for faxes older than N hours} {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to users who have not completed their fax submissions after 12 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        $dryRun = $this->option('dry-run');
        
        // Send early reminders (2 hours)
        if ($hours <= 2) {
            return $this->sendEarlyReminders($dryRun);
        }
        
        // Send regular reminders with discount (24+ hours)
        return $this->sendRegularReminders($hours, $dryRun);
    }
    
    /**
     * Send early reminder emails (2 hours after creation)
     */
    private function sendEarlyReminders($dryRun = false)
    {
        $this->info("Looking for fax jobs that need early reminders (2+ hours old)...");

        // Find fax jobs that need early reminders
        $abandonedFaxJobs = FaxJob::whereIn('status', [FaxJob::STATUS_PENDING, FaxJob::STATUS_PAYMENT_PENDING])
            ->where('created_at', '<=', now()->subHours(2))
            ->where('created_at', '>', now()->subHours(24)) // Don't send early reminder if it's been 24+ hours
            ->whereNotNull('sender_email')
            ->where('sender_email', '!=', '')
            ->where('early_reminder_sent', false)
            ->get();

        if ($abandonedFaxJobs->isEmpty()) {
            $this->info('No fax jobs found that need early reminder emails.');
            return 0;
        }

        $this->info("Found {$abandonedFaxJobs->count()} fax jobs that need early reminder emails.");

        return $this->processReminderBatch($abandonedFaxJobs, 'early', $dryRun);
    }
    
    /**
     * Send regular reminder emails with discount (24+ hours after creation)
     */
    private function sendRegularReminders($hours = 24, $dryRun = false)
    {
        $this->info("Looking for abandoned fax jobs older than {$hours} hours...");

        // Find fax jobs that need regular reminders with discount
        $abandonedFaxJobs = FaxJob::whereIn('status', [FaxJob::STATUS_PENDING, FaxJob::STATUS_PAYMENT_PENDING])
            ->where('created_at', '<=', now()->subHours($hours))
            ->whereNotNull('sender_email')
            ->where('sender_email', '!=', '')
            ->where('reminder_email_sent', false)
            ->get();

        if ($abandonedFaxJobs->isEmpty()) {
            $this->info('No abandoned fax jobs found that need reminder emails.');
            return 0;
        }

        $this->info("Found {$abandonedFaxJobs->count()} abandoned fax jobs that need reminder emails.");

        return $this->processReminderBatch($abandonedFaxJobs, 'regular', $dryRun);
    }
    
    /**
     * Process a batch of reminder emails
     */
    private function processReminderBatch($abandonedFaxJobs, $type, $dryRun)
    {
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No emails will be sent');
            $this->table(
                ['ID', 'Email', 'File', 'Created', 'Status', 'Hours Ago'],
                $abandonedFaxJobs->map(function ($job) {
                    return [
                        $job->id,
                        $job->sender_email ?: 'No email',
                        $job->file_original_name,
                        $job->created_at->format('M j, Y g:i A'),
                        $job->status,
                        now()->diffInHours($job->created_at)
                    ];
                })
            );
            return 0;
        }

        $sentCount = 0;
        $errorCount = 0;

        foreach ($abandonedFaxJobs as $faxJob) {
            try {
                $reminderType = $type === 'early' ? 'early reminder' : 'reminder with discount';
                $this->line("Sending {$reminderType} to {$faxJob->sender_email} for job #{$faxJob->id}...");

                // Send the appropriate reminder email
                if ($type === 'early') {
                    Mail::to($faxJob->sender_email)->send(new EarlyFaxReminderEmail($faxJob));
                    $faxJob->markEarlyReminderEmailSent();
                } else {
                    Mail::to($faxJob->sender_email)->send(new FaxReminderEmail($faxJob));
                    $faxJob->markReminderEmailSent();
                }

                $sentCount++;
                $this->info("  ✅ {$reminderType} sent successfully");

                Log::info("Fax {$reminderType} email sent", [
                    'fax_job_id' => $faxJob->id,
                    'recipient_email' => $faxJob->sender_email,
                    'reminder_type' => $type,
                    'hours_since_creation' => now()->diffInHours($faxJob->created_at)
                ]);

                // Small delay to avoid overwhelming the email service
                usleep(250000); // 0.25 seconds

            } catch (\Exception $e) {
                $errorCount++;
                $this->error("  ❌ Failed to send reminder: " . $e->getMessage());

                Log::error("Failed to send fax reminder email", [
                    'fax_job_id' => $faxJob->id,
                    'recipient_email' => $faxJob->sender_email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Reminder email process completed:");
        $this->info("  📧 Sent: {$sentCount}");
        if ($errorCount > 0) {
            $this->warn("  ❌ Errors: {$errorCount}");
        }

        Log::info("Fax {$type} reminder email batch completed", [
            'total_found' => $abandonedFaxJobs->count(),
            'sent_count' => $sentCount,
            'error_count' => $errorCount,
            'reminder_type' => $type
        ]);
        
        return $errorCount > 0 ? 1 : 0;
    }
}
