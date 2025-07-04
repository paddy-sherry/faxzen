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
    public $tries = 3; // Allow up to 3 attempts
    public $backoff = [30, 60]; // Retry after 30s, then 60s

    public function __construct(FaxJob $faxJob)
    {
        $this->faxJob = $faxJob;
        // Store the original local path for cleanup
        $this->originalLocalPath = $faxJob->file_path;
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
            $this->faxJob->update([
                'retry_attempts' => $this->attempts(),
                'last_retry_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Failed to send fax via Telnyx", [
                'fax_job_id' => $this->faxJob->id,
                'attempt' => $this->attempts(),
                'max_attempts' => $this->tries,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'recipient' => $this->faxJob->recipient_number,
                'from_number' => config('services.telnyx.from_number'),
                'connection_id' => config('services.telnyx.connection_id'),
                'file_path' => $this->faxJob->file_path,
                'stack_trace' => $e->getTraceAsString()
            ]);

            // If this was the last attempt, mark as failed
            if ($this->attempts() >= $this->tries) {
                $this->faxJob->update([
                    'status' => FaxJob::STATUS_FAILED,
                ]);
                
                Log::error("Fax job failed permanently", [
                    'fax_job_id' => $this->faxJob->id,
                    'final_error' => $e->getMessage(),
                ]);

                // Clean up local file
                $this->cleanupLocalFile();

                // TODO: Send failure notification email
                $this->sendFailureEmail();
            }

            throw $e; // Re-throw to trigger retry
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
