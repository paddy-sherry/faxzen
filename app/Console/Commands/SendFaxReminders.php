<?php

namespace App\Console\Commands;

use App\Models\FaxJob;
use App\Mail\FaxReminderEmail;
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
        
        $this->info("Looking for abandoned fax jobs older than {$hours} hours...");

        // Find fax jobs that are pending or payment_pending, created more than N hours ago,
        // have a sender_email, and haven't had a reminder email sent yet
        $abandonedFaxJobs = FaxJob::whereIn('status', [FaxJob::STATUS_PENDING, FaxJob::STATUS_PAYMENT_PENDING])
            ->where('created_at', '<=', now()->subHours($hours))
            ->whereNotNull('sender_email')
            ->where('sender_email', '!=', '')
            ->where('reminder_email_sent', false)
            ->get();

        if ($abandonedFaxJobs->isEmpty()) {
            $this->info('No abandoned fax jobs found that need reminder emails.');
            return;
        }

        $this->info("Found {$abandonedFaxJobs->count()} abandoned fax jobs that need reminder emails.");

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
            return;
        }

        $sentCount = 0;
        $errorCount = 0;

        foreach ($abandonedFaxJobs as $faxJob) {
            try {
                $this->line("Sending reminder to {$faxJob->sender_email} for job #{$faxJob->id}...");

                // Send the reminder email
                Mail::to($faxJob->sender_email)->send(new FaxReminderEmail($faxJob));

                // Mark as reminder sent
                $faxJob->update([
                    'reminder_email_sent' => true,
                    'reminder_email_sent_at' => now()
                ]);

                $sentCount++;
                $this->info("  ✅ Reminder sent successfully");

                Log::info("Fax reminder email sent", [
                    'fax_job_id' => $faxJob->id,
                    'recipient_email' => $faxJob->sender_email,
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

        Log::info("Fax reminder email batch completed", [
            'total_found' => $abandonedFaxJobs->count(),
            'sent_count' => $sentCount,
            'error_count' => $errorCount,
            'hours_threshold' => $hours
        ]);
    }
}
