<?php

namespace App\Console\Commands;

use App\Models\FaxJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Telnyx\Telnyx;
use Telnyx\Fax;

class CheckFaxStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fax:check-status {--job-id= : Check specific fax job ID} {--hours=2 : Check faxes from last N hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check fax delivery status via Telnyx API for jobs that may have missed webhook updates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Set up Telnyx API
        Log::debug('checking fax jobs');
        Telnyx::setApiKey(config('services.telnyx.api_key'));
        \Telnyx\Telnyx::$apiBase = config('services.telnyx.api_base');

        $jobId = $this->option('job-id');
        $hours = $this->option('hours');

        if ($jobId) {
            // Check specific job
            $faxJob = FaxJob::find($jobId);
            if (!$faxJob) {
                $this->error("Fax job {$jobId} not found");
                return;
            }
            $this->checkSingleFax($faxJob);
        } else {
            // Check recent jobs that haven't been delivered yet
            $faxJobs = FaxJob::where('status', FaxJob::STATUS_SENT)
                ->where('is_delivered', false)
                ->whereNotNull('telnyx_fax_id')
                ->where('created_at', '>=', now()->subHours($hours))
                ->get();

            $this->info("Checking {$faxJobs->count()} fax jobs from the last {$hours} hours...");

            foreach ($faxJobs as $faxJob) {
                $this->checkSingleFax($faxJob);
                usleep(500000); // Sleep 0.5 seconds between API calls
            }
        }

        Log::debug('checking fax jobs finished');
        $this->info('Fax status check completed');
    }

    /**
     * Check status of a single fax job
     */
    protected function checkSingleFax(FaxJob $faxJob)
    {
        try {
            $this->line("Checking fax job {$faxJob->id} (Telnyx ID: {$faxJob->telnyx_fax_id})...");

            // Retrieve fax status from Telnyx
            $fax = Fax::retrieve($faxJob->telnyx_fax_id);
            
            $this->info("  Current status: {$fax->status}");

            // Update our database with the latest status
            $faxJob->update([
                'telnyx_status' => $fax->status,
                'delivery_details' => json_encode($fax->toArray())
            ]);

            // Handle status changes
            switch ($fax->status) {
                case 'delivered':
                    if (!$faxJob->is_delivered) {
                        $faxJob->markDelivered($fax->status, json_encode($fax->toArray()));
                        $this->info("  ✅ Marked as delivered");
                        
                        // Send confirmation email if not already sent
                        if (!$faxJob->email_sent) {
                            try {
                                \Mail::to($faxJob->sender_email)->send(new \App\Mail\FaxDeliveryConfirmation($faxJob));
                                $faxJob->markEmailSent();
                                $this->info("  📧 Confirmation email sent successfully");
                            } catch (\Exception $e) {
                                $this->error("  ❌ Failed to send confirmation email: " . $e->getMessage());
                            }
                        }
                    }
                    break;

                case 'failed':
                    $faxJob->update([
                        'status' => FaxJob::STATUS_FAILED,
                        'error_message' => $fax->failure_reason ?? 'Fax delivery failed'
                    ]);
                    $this->error("  ❌ Marked as failed: " . ($fax->failure_reason ?? 'Unknown reason'));
                    break;

                case 'sending':
                    if (!$faxJob->is_sending) {
                        $faxJob->markSendingStarted();
                        $this->info("  📤 Marked as sending");
                    }
                    break;

                default:
                    $this->line("  ⏳ Status: {$fax->status}");
            }

            Log::info("Fax status checked via command", [
                'fax_job_id' => $faxJob->id,
                'telnyx_fax_id' => $faxJob->telnyx_fax_id,
                'old_status' => $faxJob->telnyx_status,
                'new_status' => $fax->status
            ]);

        } catch (\Exception $e) {
            $this->error("  ❌ Error checking fax {$faxJob->id}: " . $e->getMessage());
            
            Log::error("Failed to check fax status via command", [
                'fax_job_id' => $faxJob->id,
                'telnyx_fax_id' => $faxJob->telnyx_fax_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
