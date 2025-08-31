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
    protected $signature = 'fax:check-status {--job-id= : Check specific fax job ID} {--hours=2 : Check faxes from last N hours} {--send-missing-emails : Send emails for delivered faxes without confirmation emails}';

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
        Log::debug('checking fax jobs 1');
        Telnyx::setApiKey(config('services.telnyx.api_key'));
        \Telnyx\Telnyx::$apiBase = config('services.telnyx.api_base');

        $jobId = $this->option('job-id');
        $hours = $this->option('hours');
        $sendMissingEmails = $this->option('send-missing-emails');

        if ($jobId) {
           Log::debug('checking fax jobs 2');
            // Check specific job
            $faxJob = FaxJob::find($jobId);
            if (!$faxJob) {
                $this->error("Fax job {$jobId} not found");
                return;
            }
            $this->checkSingleFax($faxJob);
        } elseif ($sendMissingEmails) {
            Log::debug('checking fax jobs 3');
            // Send emails for delivered faxes that don't have confirmation emails
            $faxJobs = FaxJob::where('is_delivered', true)
                ->where('email_sent', false)
                ->whereNotNull('sender_email')
                ->get();

            $this->info("Found {$faxJobs->count()} delivered fax jobs without confirmation emails...");

            foreach ($faxJobs as $faxJob) {
                Log::debug('checking fax jobs 4');
                $this->sendMissingEmail($faxJob);
                usleep(250000); // Sleep 0.25 seconds between emails
            }
        } else {
            Log::debug('checking fax jobs 5');
            // Check recent jobs that haven't been delivered yet
            $faxJobs = FaxJob::where('status', FaxJob::STATUS_SENT)
                ->where('is_delivered', false)
                ->whereNotNull('telnyx_fax_id')
                ->where('created_at', '>=', now()->subHours($hours))
                ->get();
                Log::debug('checking fax jobs 6');
            $this->info("Checking {$faxJobs->count()} fax jobs from the last {$hours} hours...");
            
            foreach ($faxJobs as $faxJob) {
                Log::debug('checking fax jobs 7');
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
            Log::debug('checking fax jobs 8');
            $this->line("Checking fax job {$faxJob->id} (Telnyx ID: {$faxJob->telnyx_fax_id})...");

            // Retrieve fax status from Telnyx
            $fax = Fax::retrieve($faxJob->telnyx_fax_id);
            Log::debug('checking fax jobs 9');
            
            $this->info("  Current status: {$fax->status}");

            // Update our database with the latest status
            $faxJob->update([
                'telnyx_status' => $fax->status,
                'delivery_details' => json_encode($fax->toArray())
            ]);
            Log::debug('checking fax jobs 10');
            // Handle status changes
            switch ($fax->status) {
                case 'delivered':
                    Log::debug('checking fax jobs 11');
                    if (!$faxJob->is_delivered) {
                        $faxJob->markDelivered($fax->status, json_encode($fax->toArray()));
                        $this->info("  ✅ Marked as delivered");
                    }
                    
                    // Send confirmation email if not already sent (regardless of when it was marked delivered)
                    if (!$faxJob->email_sent) {
                        Log::debug('checking fax jobs 12');
                        Log::debug('sending email 12.1 '. $faxJob->id);
                        try {
                            \Mail::to($faxJob->sender_email)->bcc('faxzen.com+656498d49b@invite.trustpilot.com')->send(new \App\Mail\FaxDeliveryConfirmation($faxJob));
                            $faxJob->markEmailSent();
                            Log::debug('checking fax jobs 13');
                            $this->info("  📧 Confirmation email sent successfully");
                        } catch (\Exception $e) {
                            $this->error("  ❌ Failed to send confirmation email: " . $e->getMessage());
                            Log::error("Failed to send fax confirmation email", [
                                'fax_job_id' => $faxJob->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    } else {
                        $this->line("  📧 Email already sent");
                    }
                    break;

                case 'failed':
                    Log::debug('checking fax jobs 14');
                    
                    // Check for specific failure reasons that might be retryable
                    $failureReason = $fax->failure_reason ?? 'Fax delivery failed';
                    $isRetryableFailure = in_array($failureReason, [
                        'receiver_call_dropped',
                        'sender_call_dropped', 
                        'timeout',
                        'busy'
                    ]);
                    
                    // Check for ECM-related errors that need special handling
                    $isEcmError = str_contains(strtolower($failureReason), 'ecm') || 
                                  str_contains(strtolower($failureReason), 'error_correction');
                    
                    $faxJob->update([
                        'status' => FaxJob::STATUS_FAILED,
                        'error_message' => $failureReason
                    ]);
                    
                    if ($isEcmError) {
                        $this->error("  ❌ Marked as failed (ECM compatibility): " . $failureReason);
                        $this->line("  🔧 ECM error: Receiving fax machine has ECM compatibility issues");
                        $this->line("  💡 Suggestion: Contact recipient to disable ECM on their fax machine");
                    } elseif ($isRetryableFailure && $faxJob->canRetry()) {
                        $this->error("  ❌ Marked as failed (retryable): " . $failureReason);
                        $this->line("  🔄 This error type can often be resolved by retrying");
                    } else {
                        $this->error("  ❌ Marked as failed: " . $failureReason);
                    }
                    break;

                case 'sending':
                    Log::debug('checking fax jobs 15');
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

    /**
     * Send missing confirmation email for delivered fax
     */
    protected function sendMissingEmail(FaxJob $faxJob)
    {
        try {
            Log::debug('checking fax jobs 16');
            $this->line("Sending email for fax job {$faxJob->id} to {$faxJob->sender_email}...");
            
            \Mail::to($faxJob->sender_email)->bcc('faxzen.com+656498d49b@invite.trustpilot.com')->send(new \App\Mail\FaxDeliveryConfirmation($faxJob));
            $faxJob->markEmailSent();
            Log::debug('checking fax jobs 17');
            $this->info("  ✅ Confirmation email sent successfully");
            
            Log::info("Missing fax confirmation email sent", [
                'fax_job_id' => $faxJob->id,
                'recipient_email' => $faxJob->sender_email
            ]);
            
        } catch (\Exception $e) {
            $this->error("  ❌ Failed to send email for fax {$faxJob->id}: " . $e->getMessage());
            
            Log::error("Failed to send missing fax confirmation email", [
                'fax_job_id' => $faxJob->id,
                'recipient_email' => $faxJob->sender_email,
                'error' => $e->getMessage()
            ]);
        }
    }
}
