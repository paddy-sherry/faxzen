<?php

namespace App\Jobs;

use App\Models\FaxJob;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Telnyx\Telnyx;
use Telnyx\Fax;

class SendFaxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $faxJob;
    protected $originalLocalPath;
    public $tries = 20; // Allow up to 20 attempts for busy lines
    public $timeout = 300; // 5 minute timeout per attempt

    public function __construct(FaxJob $faxJob)
    {
        $this->faxJob = $faxJob;
        // Store the original local path for cleanup
        $this->originalLocalPath = $faxJob->file_path;
    }

    /**
     * Calculate intelligent backoff timing based on error type and attempt number
     */
    public function backoff()
    {
        $attempt = $this->attempts();
        $currentHour = now()->hour;
        
        // Get the last error message to determine retry strategy
        $errorMessage = strtolower($this->faxJob->error_message ?? '');
        $isBusyError = str_contains($errorMessage, 'busy') || str_contains($errorMessage, 'user_busy');
        $isTemporaryError = str_contains($errorMessage, 'timeout') || 
                           str_contains($errorMessage, 'call_dropped') ||
                           str_contains($errorMessage, 'network');

        // Base delays for different error types
        if ($isBusyError) {
            // For busy lines: Start with 5 min, increase to 15 min
            $baseDelay = min(300 + ($attempt * 120), 900); // 5-15 minutes
            
            // Avoid retrying during likely off-business hours (10 PM - 7 AM)
            if ($currentHour >= 22 || $currentHour <= 7) {
                // Wait until 8 AM
                $nextBusinessHour = now()->hour(8)->minute(0)->second(0);
                if ($currentHour >= 22) {
                    $nextBusinessHour = $nextBusinessHour->addDay();
                }
                $waitUntilBusiness = $nextBusinessHour->diffInSeconds(now());
                $baseDelay = max($baseDelay, $waitUntilBusiness);
            }
            
        } elseif ($isTemporaryError) {
            // For temporary errors: Exponential backoff 30s, 60s, 2min, 4min, 8min
            $baseDelay = min(30 * pow(2, $attempt - 1), 480); // Max 8 minutes
            
        } else {
            // For unknown errors: Conservative exponential backoff
            $baseDelay = min(60 * pow(2, $attempt - 1), 600); // Max 10 minutes
        }

        // Add some jitter to prevent thundering herd
        $jitter = rand(-30, 30);
        $delay = max(30, $baseDelay + $jitter); // Minimum 30 seconds

        Log::info("Fax retry scheduled", [
            'fax_job_id' => $this->faxJob->id,
            'attempt' => $attempt,
            'delay_seconds' => $delay,
            'delay_minutes' => round($delay / 60, 1),
            'error_type' => $isBusyError ? 'busy' : ($isTemporaryError ? 'temporary' : 'unknown'),
            'current_hour' => $currentHour,
            'error_message' => $this->faxJob->error_message
        ]);

        return $delay;
    }

    public function handle(): void
    {
        // Mark as sending started
        $this->faxJob->markSendingStarted();

        // Set Telnyx API key only
        $apiKey = config('services.telnyx.api_key');
        Log::info("Telnyx Configuration", [
            'has_api_key' => !empty($apiKey),
            'connection_id' => config('services.telnyx.connection_id'),
        ]);
        Telnyx::setApiKey($apiKey);
        \Telnyx\Telnyx::$apiBase = config('services.telnyx.api_base');

        try {
            // Move file from local to R2 if needed
            $r2FilePath = $this->ensureFileOnR2();

            // Use the R2 file path directly
            $finalFilePath = $r2FilePath;

            $mediaUrl = $this->getFileUrl($finalFilePath);
            
            Log::info("Preparing to send fax via Telnyx", [
                'fax_job_id' => $this->faxJob->id,
                'recipient' => $this->faxJob->recipient_number,
                'from_number' => config('services.telnyx.from_number', '+18001234567'),
                'connection_id' => config('services.telnyx.connection_id'),
                'media_url' => $mediaUrl,
                'file_path' => $finalFilePath,
                'file_name' => $this->faxJob->file_original_name,
                'file_size' => $this->faxJob->original_file_size
            ]);

            // Create a fax using Telnyx API
            $faxCreateParams = [
                'connection_id' => config('services.telnyx.connection_id'),
                'media_url' => $mediaUrl,
                'to' => $this->faxJob->recipient_number,
                'from' => config('services.telnyx.from_number', '+18001234567'), // Default or configured number
                'quality' => 'high',
                'store_media' => true,
            ];

            // Note: No webhook URL - using console job polling for status updates

            $fax = Fax::create($faxCreateParams);

            Log::info('Telnyx Fax API response received', [
                'fax_job_id' => $this->faxJob->id,
                'fax_response' => is_object($fax) ? $fax->toArray() : $fax,
                'fax_id' => $fax->id ?? 'unknown',
                'status' => $fax->status ?? 'unknown'
            ]);

            // Update the fax job with Telnyx fax ID - console job will poll for delivery status
            $this->faxJob->update([
                'status' => FaxJob::STATUS_SENT,
                'telnyx_fax_id' => $fax->id,
                'retry_attempts' => $this->attempts(),
                'telnyx_status' => $fax->status ?? 'queued',
                'delivery_details' => json_encode($fax->toArray())
            ]);

            Log::info("Fax submitted to Telnyx successfully", [
                'fax_job_id' => $this->faxJob->id,
                'telnyx_fax_id' => $fax->id,
                'recipient' => $this->faxJob->recipient_number,
                'telnyx_status' => $fax->status ?? 'queued',
                'note' => 'Console job will check delivery status every 2 minutes'
            ]);

            // Clean up local file if it exists
            $this->cleanupLocalFile();

            // Note: Email will be sent via console job when delivery is confirmed

        } catch (\Exception $e) {
            // Parse Telnyx error for specific failure reasons
            $errorMessage = $e->getMessage();
            $isUserBusy = str_contains(strtolower($errorMessage), 'busy') || 
                         str_contains(strtolower($errorMessage), 'user_busy');
            $isTemporaryError = str_contains(strtolower($errorMessage), 'timeout') || 
                               str_contains(strtolower($errorMessage), 'call_dropped') ||
                               str_contains(strtolower($errorMessage), 'network') ||
                               str_contains(strtolower($errorMessage), 'service_unavailable');

            $this->faxJob->update([
                'retry_attempts' => $this->attempts(),
                'last_retry_at' => now(),
                'error_message' => $errorMessage,
            ]);

            Log::error("Failed to send fax via Telnyx", [
                'fax_job_id' => $this->faxJob->id,
                'attempt' => $this->attempts(),
                'max_attempts' => $this->tries,
                'error_message' => $errorMessage,
                'error_type' => $isUserBusy ? 'user_busy' : ($isTemporaryError ? 'temporary' : 'unknown'),
                'error_code' => $e->getCode(),
                'recipient' => $this->faxJob->recipient_number,
                'from_number' => config('services.telnyx.from_number'),
                'connection_id' => config('services.telnyx.connection_id'),
                'will_retry' => $this->attempts() < $this->tries,
                'next_retry_in_minutes' => $this->attempts() < $this->tries ? round($this->backoff() / 60, 1) : 0
            ]);

            // If this was the last attempt, mark as failed
            if ($this->attempts() >= $this->tries) {
                $finalStatus = $isUserBusy ? 'failed_busy_line' : 'failed_permanent';
                
                $this->faxJob->update([
                    'status' => FaxJob::STATUS_FAILED,
                    'error_message' => $this->getFinalErrorMessage($errorMessage, $isUserBusy, $isTemporaryError)
                ]);
                
                Log::error("Fax job failed permanently after {$this->tries} attempts", [
                    'fax_job_id' => $this->faxJob->id,
                    'final_error' => $errorMessage,
                    'error_type' => $isUserBusy ? 'user_busy' : ($isTemporaryError ? 'temporary' : 'unknown'),
                    'total_attempts' => $this->tries,
                    'recipient' => $this->faxJob->recipient_number,
                    'suggestion' => $isUserBusy ? 'Try again during business hours or contact recipient' : 'Check recipient fax number and try again'
                ]);

                // Clean up local file
                $this->cleanupLocalFile();

                // Send failure notification email
                $this->sendFailureEmail();
            }

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Generate user-friendly final error message based on error type
     */
    protected function getFinalErrorMessage($originalError, $isUserBusy, $isTemporaryError)
    {
        if ($isUserBusy) {
            return "Recipient's fax line was busy after 20 attempts over " . 
                   round($this->tries * 10 / 60, 1) . " hours. The fax machine may be turned off, " .
                   "on a long phone call, or experiencing technical issues. Please try again later " .
                   "or contact the recipient to ensure their fax machine is available.";
        } elseif ($isTemporaryError) {
            return "Temporary network or connection issues prevented delivery after 20 attempts. " .
                   "This could be due to network congestion, service outages, or connection problems. " .
                   "Please verify the fax number and try again later.";
        } else {
            return "Fax delivery failed after 20 attempts. This could be due to an invalid fax number, " .
                   "a disconnected line, or compatibility issues with the recipient's fax machine. " .
                   "Please verify the fax number is correct and active. Original error: " . $originalError;
        }
    }

    /**
     * Ensure file is available on R2 storage
     */
    protected function ensureFileOnR2(): string
    {
        Log::info("Checking file location", [
            'fax_job_id' => $this->faxJob->id,
            'file_path' => $this->faxJob->file_path,
            'starts_with_fax_documents' => str_starts_with($this->faxJob->file_path, 'fax_documents/'),
            'starts_with_temp_fax_documents' => str_starts_with($this->faxJob->file_path, 'temp_fax_documents/')
        ]);

        // Check if file is already on R2
        if (str_starts_with($this->faxJob->file_path, 'fax_documents/')) {
            // Already on R2
            if (\Storage::disk('r2')->exists($this->faxJob->file_path)) {
                Log::info("File already on R2", [
                    'fax_job_id' => $this->faxJob->id,
                    'r2_path' => $this->faxJob->file_path
                ]);
                return $this->faxJob->file_path;
            } else {
                Log::warning("File path suggests R2 but file not found on R2", [
                    'fax_job_id' => $this->faxJob->id,
                    'r2_path' => $this->faxJob->file_path
                ]);
            }
        }

        // File is local (starts with temp_fax_documents/), need to move to R2
        $localPath = $this->faxJob->file_path;
        
        if (!Storage::disk('local')->exists($localPath)) {
            throw new \Exception("Local file not found: {$localPath}");
        }

        Log::info("Moving file from local to R2", [
            'fax_job_id' => $this->faxJob->id,
            'local_path' => $localPath
        ]);

        // Create R2 path
        $r2Path = 'fax_documents/' . basename($localPath);
        
        // Get file content from local storage
        $fileContent = Storage::disk('local')->get($localPath);
        
        // Upload to R2
        Storage::disk('r2')->put($r2Path, $fileContent);
        
        // Update fax job with R2 path
        $this->faxJob->update(['file_path' => $r2Path]);
        
        Log::info("File moved to R2 successfully", [
            'fax_job_id' => $this->faxJob->id,
            'r2_path' => $r2Path
        ]);

        return $r2Path;
    }

    /**
     * Clean up local temporary file
     */
    protected function cleanupLocalFile(): void
    {
        // Use the original local path stored during construction
        $localPath = $this->originalLocalPath;
        
        if (Storage::disk('local')->exists($localPath)) {
            Storage::disk('local')->delete($localPath);
            Log::info("Cleaned up local file", [
                'fax_job_id' => $this->faxJob->id,
                'local_path' => $localPath
            ]);
        } else {
            Log::info("Local file already deleted or not found", [
                'fax_job_id' => $this->faxJob->id,
                'local_path' => $localPath
            ]);
        }
    }



    protected function getFileUrl(string $filePath): string
    {
        // Generate a temporary signed URL for 10 minutes
        return \Storage::disk('r2')->temporaryUrl($filePath, now()->addMinutes(10));
    }

    protected function sendConfirmationEmail(): void
    {
        try {
            // Mark email as sent
            $this->faxJob->markEmailSent();
            
            // TODO: Implement actual email notification
            // You can use Laravel's Mail system here
            Log::info("Confirmation email marked as sent", [
                'fax_job_id' => $this->faxJob->id,
                'recipient_email' => $this->faxJob->sender_email
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to mark email as sent", [
                'fax_job_id' => $this->faxJob->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function sendFailureEmail(): void
    {
        // TODO: Implement failure notification email
        Log::info("Should send failure email to: " . $this->faxJob->sender_email);
    }

    public function failed(\Throwable $exception): void
    {
        // This method is called when the job fails permanently
        Log::error("SendFaxJob failed permanently", [
            'fax_job_id' => $this->faxJob->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
