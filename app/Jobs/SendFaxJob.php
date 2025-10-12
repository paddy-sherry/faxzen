<?php

namespace App\Jobs;

use App\Models\FaxJob;
use App\Mail\FaxDeliveryFailed;
use App\Services\CoverPageService;
use App\Services\PdfMergeService;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Telnyx\Telnyx;
use Telnyx\Fax;
use App\Services\TimezoneService;

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
     * Quick retries first, then geographic timezone awareness for persistent issues
     */
    public function backoff()
    {
        $attempt = $this->attempts();
        
        // Get the last error message to determine retry strategy
        $errorMessage = strtolower($this->faxJob->error_message ?? '');
        $isBusyError = str_contains($errorMessage, 'busy') || str_contains($errorMessage, 'user_busy');
        $isTemporaryError = str_contains($errorMessage, 'timeout') || 
                           str_contains($errorMessage, 'call_dropped') ||
                           str_contains($errorMessage, 'network');

        // For busy lines: Use quick retries first, then geographic logic
        if ($isBusyError) {
            return $this->calculateBusyLineDelay($attempt);
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
            'error_message' => $this->faxJob->error_message
        ]);

        return $delay;
    }

    /**
     * Calculate delay for busy line errors with two-stage approach:
     * Stage 1: Quick retries (attempts 2-6) - 2, 4, 6, 8, 10 minutes
     * Stage 2: Geographic awareness (attempts 7+) - Wait for business hours
     */
    protected function calculateBusyLineDelay(int $attempt): int
    {
        $recipientTimezone = TimezoneService::detectTimezoneFromPhoneNumber($this->faxJob->recipient_number);
        $businessHoursInfo = TimezoneService::getBusinessHoursInfo($recipientTimezone);
        
        // Stage 1: Quick retries for first 5 attempts (may resolve quickly)
        if ($attempt <= 6) {
            $baseDelay = min(120 * $attempt, 600); // 2, 4, 6, 8, 10 minutes (max 10 min)
            
            // Update fax job to indicate quick retry stage
            $this->faxJob->update(['retry_stage' => 'quick_retry']);
            
            Log::info("Busy line quick retry scheduled", [
                'fax_job_id' => $this->faxJob->id,
                'attempt' => $attempt,
                'stage' => 'quick_retry',
                'delay_minutes' => round($baseDelay / 60, 1),
                'recipient_phone' => $this->faxJob->recipient_number,
                'recipient_timezone' => $recipientTimezone,
                'strategy' => 'Quick retries - many busy lines clear up within 10-30 minutes'
            ]);
            
        } else {
            // Stage 2: Geographic awareness for persistent busy lines (attempts 7+)
            $baseDelay = min(300 + (($attempt - 6) * 120), 900); // 5-15 minutes base
            
            // Apply business hours logic for persistent busy lines
            if (!$businessHoursInfo['is_business_hours']) {
                $waitUntilBusiness = $businessHoursInfo['delay_until_business'];
                $baseDelay = max($baseDelay, $waitUntilBusiness);
                
                // Update fax job to indicate business hours waiting stage
                $this->faxJob->update(['retry_stage' => 'business_hours_wait']);
                
                Log::info("Busy line waiting for recipient business hours", [
                    'fax_job_id' => $this->faxJob->id,
                    'attempt' => $attempt,
                    'stage' => 'business_hours_wait',
                    'recipient_timezone' => $recipientTimezone,
                    'recipient_current_time' => $businessHoursInfo['current_time'],
                    'is_weekend' => $businessHoursInfo['is_weekend'],
                    'next_business_hour' => $businessHoursInfo['next_business_hour'],
                    'delay_hours' => round($waitUntilBusiness / 3600, 1),
                    'strategy' => 'Persistent busy line - waiting for recipient business hours'
                ]);
            } else {
                // Still business hours, continue with regular busy line timing
                $this->faxJob->update(['retry_stage' => 'persistent_busy']);
                
                Log::info("Persistent busy line retry during business hours", [
                    'fax_job_id' => $this->faxJob->id,
                    'attempt' => $attempt,
                    'stage' => 'persistent_busy',
                    'delay_minutes' => round($baseDelay / 60, 1),
                    'recipient_timezone' => $recipientTimezone,
                    'strategy' => 'Persistent busy line during business hours - continue regular retries'
                ]);
            }
        }

        // Add jitter
        $jitter = rand(-30, 30);
        $delay = max(30, $baseDelay + $jitter);

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
            // Log retry attempt
            $this->faxJob->addRetryLog('attempting', 'Attempting to send fax via Telnyx', [
                'attempt' => $this->attempts(),
                'max_attempts' => $this->tries,
                'recipient' => $this->faxJob->recipient_number
            ]);
            
            // Clear retry stage on successful attempt
            $this->faxJob->update(['retry_stage' => null]);
            
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

            // Log successful submission
            $this->faxJob->addRetryLog('success', 'Fax successfully submitted to Telnyx', [
                'telnyx_fax_id' => $fax->id,
                'telnyx_status' => $fax->status ?? 'queued',
                'attempt' => $this->attempts()
            ]);

            Log::info("Fax submitted to Telnyx successfully", [
                'fax_job_id' => $this->faxJob->id,
                'telnyx_fax_id' => $fax->id,
                'recipient' => $this->faxJob->recipient_number,
                'telnyx_status' => $fax->status ?? 'queued',
                'note' => 'Console job will check delivery status every 2 minutes'
            ]);

            // Clean up temporary files
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

            // Log failure
            $this->faxJob->addRetryLog('failed', 'Fax sending failed', [
                'error' => $errorMessage,
                'attempt' => $this->attempts(),
                'max_attempts' => $this->tries,
                'will_retry' => $this->attempts() < $this->tries,
                'next_retry_in_minutes' => $this->attempts() < $this->tries ? round($this->backoff() / 60, 1) : 0
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

        // Handle multiple files or single file
        $filePaths = $this->faxJob->getAllFilePaths();
        
        if (empty($filePaths)) {
            throw new \Exception("No files found for fax job");
        }

        // Verify all files exist locally
        foreach ($filePaths as $filePath) {
            if (!Storage::disk('local')->exists($filePath)) {
                throw new \Exception("Local file not found: {$filePath}");
            }
        }

        // Process cover page if enabled
        $finalDocumentPath = $filePaths[0]; // Default to first file
        $coverPagePath = null;
        $mergedPath = null;
        $pdfMergeService = new PdfMergeService(); // Initialize for cleanup
        
        if ($this->faxJob->include_cover_page) {
            Log::info("Processing cover page for fax", [
                'fax_job_id' => $this->faxJob->id,
                'file_count' => count($filePaths),
                'file_paths' => $filePaths
            ]);
            
            $coverPageService = new CoverPageService();
            
            try {
                // Generate cover page
                $coverPagePath = $coverPageService->generateCoverPage($this->faxJob);
                
                if ($coverPagePath) {
                    // Merge cover page with all documents
                    if ($this->faxJob->hasMultipleFiles()) {
                        $mergedPath = $pdfMergeService->mergeMultiplePdfs($coverPagePath, $filePaths);
                    } else {
                        $mergedPath = $pdfMergeService->mergePdfs($coverPagePath, $filePaths[0]);
                    }
                    
                    if ($mergedPath) {
                        $finalDocumentPath = $mergedPath;
                        Log::info('Cover page merged successfully', [
                            'fax_job_id' => $this->faxJob->id,
                            'cover_page_path' => $coverPagePath,
                            'merged_path' => $mergedPath,
                            'file_count' => count($filePaths)
                        ]);
                    } else {
                        Log::warning('Cover page merge failed, using original document', [
                            'fax_job_id' => $this->faxJob->id
                        ]);
                    }
                } else {
                    Log::warning('Cover page generation failed, using original document', [
                        'fax_job_id' => $this->faxJob->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Cover page processing failed', [
                    'fax_job_id' => $this->faxJob->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else if ($this->faxJob->hasMultipleFiles()) {
            // If we have multiple files but no cover page, merge them together
            Log::info("Processing multiple files for fax (no cover page)", [
                'fax_job_id' => $this->faxJob->id,
                'file_count' => count($filePaths),
                'file_paths' => $filePaths
            ]);
            
            try {
                // Merge all documents together
                $mergedPath = $pdfMergeService->mergeMultiplePdfs(null, $filePaths);
                
                if ($mergedPath) {
                    $finalDocumentPath = $mergedPath;
                    Log::info('Multiple files merged successfully', [
                        'fax_job_id' => $this->faxJob->id,
                        'merged_path' => $mergedPath,
                        'file_count' => count($filePaths)
                    ]);
                } else {
                    Log::warning('Multiple file merge failed, using first file only', [
                        'fax_job_id' => $this->faxJob->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Multiple file processing failed', [
                    'fax_job_id' => $this->faxJob->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Moving final document from local to R2", [
            'fax_job_id' => $this->faxJob->id,
            'original_paths' => $filePaths,
            'final_document_path' => $finalDocumentPath,
            'has_cover_page' => $this->faxJob->include_cover_page
        ]);

        // Create R2 path with cover page indicator in filename
        $originalBasename = basename($filePaths[0]);
        $r2Filename = $this->faxJob->include_cover_page 
            ? str_replace('.', '_with_cover.', $originalBasename)
            : $originalBasename;
        $r2Path = 'fax_documents/' . $r2Filename;
        
        // Get file content from final document (could be merged with cover page)
        $fileContent = Storage::disk('local')->get($finalDocumentPath);
        
        // Upload to R2
        Storage::disk('r2')->put($r2Path, $fileContent);
        
        // Clean up temporary cover page files
        if ($coverPagePath) {
            $coverPageService = new CoverPageService();
            $coverPageService->cleanupCoverPage($coverPagePath);
        }
        if ($mergedPath && $mergedPath !== $filePaths[0]) {
            $pdfMergeService->cleanupMergedFile($mergedPath);
        }
        
        // Clean up any converted files
        $pdfMergeService->cleanupConvertedFiles();
        
        // Update fax job with R2 path
        $this->faxJob->update(['file_path' => $r2Path]);
        
        Log::info("File moved to R2 successfully", [
            'fax_job_id' => $this->faxJob->id,
            'r2_path' => $r2Path
        ]);

        return $r2Path;
    }

    /**
     * Clean up local temporary files
     */
    protected function cleanupLocalFile(): void
    {
        // Clean up all local files
        $filePaths = $this->faxJob->getAllFilePaths();
        
        foreach ($filePaths as $filePath) {
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
                Log::info("Cleaned up local file", [
                    'fax_job_id' => $this->faxJob->id,
                    'local_path' => $filePath
                ]);
            } else {
                Log::info("Local file already deleted or not found", [
                    'fax_job_id' => $this->faxJob->id,
                    'local_path' => $filePath
                ]);
            }
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
        try {
            if (!$this->faxJob->failure_email_sent) {
                \Mail::to($this->faxJob->sender_email)->send(new \App\Mail\FaxDeliveryFailed($this->faxJob));
                $this->faxJob->markFailureEmailSent();
                
                Log::info('Failure notification email sent from SendFaxJob', [
                    'fax_job_id' => $this->faxJob->id,
                    'recipient_email' => $this->faxJob->sender_email
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send failure notification email from SendFaxJob', [
                'fax_job_id' => $this->faxJob->id,
                'error' => $e->getMessage()
            ]);
        }
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
