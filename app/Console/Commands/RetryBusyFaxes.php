<?php

namespace App\Console\Commands;

use App\Models\FaxJob;
use App\Jobs\SendFaxJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryBusyFaxes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fax:retry-busy 
                            {--job-id= : Retry specific fax job ID}
                            {--hours=24 : Retry busy faxes from last N hours}
                            {--dry-run : Show what would be retried without actually retrying}
                            {--force : Force retry even if retry limit reached}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry fax jobs that failed due to busy lines during business hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jobId = $this->option('job-id');
        $hours = $this->option('hours');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $currentHour = now()->hour;

        // Only run during business hours (8 AM - 6 PM) unless forced
        if (!$force && ($currentHour < 8 || $currentHour > 18)) {
            $this->warn("Current time is outside business hours ({$currentHour}:00). Use --force to retry anyway.");
            $this->info("Busy fax retries are automatically scheduled for business hours to improve success rates.");
            return;
        }

        if ($jobId) {
            // Retry specific job
            $faxJob = FaxJob::find($jobId);
            if (!$faxJob) {
                $this->error("Fax job {$jobId} not found");
                return;
            }
            
            $this->retrySpecificJob($faxJob, $dryRun, $force);
        } else {
            // Find failed jobs with busy line errors
            $query = FaxJob::where('status', FaxJob::STATUS_FAILED)
                ->where('created_at', '>=', now()->subHours($hours))
                ->where(function($q) {
                    $q->where('error_message', 'like', '%busy%')
                      ->orWhere('error_message', 'like', '%user_busy%');
                });

            if (!$force) {
                $query->where('retry_attempts', '<', 20);
            }

            $faxJobs = $query->orderBy('created_at', 'desc')->get();

            $this->info("Found {$faxJobs->count()} busy fax jobs from the last {$hours} hours");

            if ($faxJobs->isEmpty()) {
                $this->info("No busy fax jobs found to retry.");
                return;
            }

            foreach ($faxJobs as $faxJob) {
                $this->retrySpecificJob($faxJob, $dryRun, $force);
                
                if (!$dryRun) {
                    // Small delay between retries to avoid overwhelming the system
                    usleep(250000); // 0.25 seconds
                }
            }
        }

        if ($dryRun) {
            $this->info("Dry run completed. Use without --dry-run to actually retry the fax jobs.");
        } else {
            $this->info("Busy fax retry process completed.");
        }
    }

    /**
     * Retry a specific fax job
     */
    protected function retrySpecificJob(FaxJob $faxJob, $dryRun = false, $force = false)
    {
        $canRetry = $force || $faxJob->canRetry();
        
        $this->line("Fax Job #{$faxJob->id}:");
        $this->line("  Recipient: {$faxJob->recipient_number}");
        $this->line("  Created: {$faxJob->created_at->format('M j, Y g:i A')}");
        $this->line("  Attempts: {$faxJob->retry_attempts}");
        $this->line("  Error: {$faxJob->error_message}");
        $this->line("  Can Retry: " . ($canRetry ? 'Yes' : 'No'));

        if (!$canRetry) {
            $this->warn("  ❌ Retry limit reached. Use --force to override.");
            return;
        }

        if ($dryRun) {
            $this->info("  🔄 Would retry this job");
            return;
        }

        try {
            // Reset the job status and dispatch for retry
            $faxJob->update([
                'status' => FaxJob::STATUS_PAID,
                'telnyx_status' => null,
                'telnyx_fax_id' => null,
                'is_sending' => false,
                'is_delivered' => false,
                // Don't reset retry_attempts - keep the history
            ]);

            // Dispatch the job
            SendFaxJob::dispatch($faxJob);
            
            $this->info("  ✅ Retry job dispatched");
            
            Log::info("Busy fax job manually retried", [
                'fax_job_id' => $faxJob->id,
                'recipient' => $faxJob->recipient_number,
                'previous_attempts' => $faxJob->retry_attempts,
                'retry_reason' => 'manual_busy_retry'
            ]);

        } catch (\Exception $e) {
            $this->error("  ❌ Failed to retry: {$e->getMessage()}");
            
            Log::error("Failed to manually retry busy fax job", [
                'fax_job_id' => $faxJob->id,
                'error' => $e->getMessage()
            ]);
        }

        $this->line("");
    }
}
