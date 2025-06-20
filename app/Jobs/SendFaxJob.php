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
    public $tries = 3; // Allow up to 3 attempts
    public $backoff = [30, 60]; // Retry after 30s, then 60s

    public function __construct(FaxJob $faxJob)
    {
        $this->faxJob = $faxJob;
    }

    public function handle(): void
    {
        // Set Telnyx API key only
        $apiKey = config('services.telnyx.api_key');
        Log::info("Telnyx Configuration", [
            'has_api_key' => !empty($apiKey),
            'connection_id' => config('services.telnyx.connection_id'),
        ]);
        Telnyx::setApiKey($apiKey);
        \Telnyx\Telnyx::$apiBase = config('services.telnyx.api_base');

        try {
            // Check if the file exists on R2
            if (!\Storage::disk('r2')->exists($this->faxJob->file_path)) {
                throw new \Exception("File not found on R2: {$this->faxJob->file_path}");
            }

            $mediaUrl = $this->getFileUrl($this->faxJob->file_path);
            
            Log::info("Preparing to send fax via Telnyx", [
                'fax_job_id' => $this->faxJob->id,
                'recipient' => $this->faxJob->recipient_number,
                'from_number' => config('services.telnyx.from_number', '+18001234567'),
                'connection_id' => config('services.telnyx.connection_id'),
                'media_url' => $mediaUrl,
                'file_path' => $this->faxJob->file_path,
                'file_name' => $this->faxJob->file_original_name
            ]);

            // Create a fax using Telnyx API
            $fax = Fax::create([
                'connection_id' => config('services.telnyx.connection_id'),
                'media_url' => $mediaUrl,
                'to' => $this->faxJob->recipient_number,
                'from' => config('services.telnyx.from_number', '+18001234567'), // Default or configured number
                'quality' => 'high',
                'store_media' => true,
            ]);

            Log::info('Telnyx Fax API response received', [
                'fax_job_id' => $this->faxJob->id,
                'fax_response' => is_object($fax) ? $fax->toArray() : $fax,
                'fax_id' => $fax->id ?? 'unknown',
                'status' => $fax->status ?? 'unknown'
            ]);

            // Update the fax job with Telnyx fax ID and mark as sent
            $this->faxJob->update([
                'status' => FaxJob::STATUS_SENT,
                'telnyx_fax_id' => $fax->id,
                'retry_attempts' => $this->attempts(),
            ]);

            Log::info("Fax sent successfully", [
                'fax_job_id' => $this->faxJob->id,
                'telnyx_fax_id' => $fax->id,
                'recipient' => $this->faxJob->recipient_number,
            ]);

            // TODO: Send confirmation email to sender
            $this->sendConfirmationEmail();

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

                // TODO: Send failure notification email
                $this->sendFailureEmail();
            }

            throw $e; // Re-throw to trigger retry
        }
    }

    protected function getFileUrl(string $filePath): string
    {
        // Generate a temporary signed URL for 10 minutes
        return \Storage::disk('r2')->temporaryUrl($filePath, now()->addMinutes(10));
    }

    protected function sendConfirmationEmail(): void
    {
        // TODO: Implement email notification
        // You can use Laravel's Mail system here
        Log::info("Should send confirmation email to: " . $this->faxJob->sender_email);
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
