<?php

namespace App\Http\Controllers;

use App\Models\FaxJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelnyxWebhookController extends Controller
{
    /**
     * Handle Telnyx webhook events
     */
    public function handle(Request $request)
    {
        Log::info('Telnyx webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        $payload = $request->all();
        
        // Verify this is a fax event
        if (!isset($payload['data']['event_type']) || !str_contains($payload['data']['event_type'], 'fax')) {
            Log::info('Non-fax webhook event received', ['event_type' => $payload['data']['event_type'] ?? 'unknown']);
            return response()->json(['status' => 'ignored']);
        }

        $eventType = $payload['data']['event_type'];
        $faxData = $payload['data']['payload'] ?? [];
        $faxId = $faxData['id'] ?? null;

        if (!$faxId) {
            Log::warning('Webhook received without fax ID');
            return response()->json(['status' => 'error', 'message' => 'No fax ID provided']);
        }

        // Find the fax job by Telnyx fax ID
        $faxJob = FaxJob::where('telnyx_fax_id', $faxId)->first();
        
        if (!$faxJob) {
            Log::warning('Fax job not found for Telnyx ID', ['telnyx_fax_id' => $faxId]);
            return response()->json(['status' => 'error', 'message' => 'Fax job not found']);
        }

        Log::info('Processing fax webhook', [
            'fax_job_id' => $faxJob->id,
            'telnyx_fax_id' => $faxId,
            'event_type' => $eventType,
            'fax_status' => $faxData['status'] ?? 'unknown'
        ]);

        // Handle different event types
        switch ($eventType) {
            case 'fax.sending':
                $this->handleFaxSending($faxJob, $faxData);
                break;
                
            case 'fax.delivered':
                $this->handleFaxDelivered($faxJob, $faxData);
                break;
                
            case 'fax.failed':
                $this->handleFaxFailed($faxJob, $faxData);
                break;
                
            case 'fax.media.processed':
                $this->handleFaxMediaProcessed($faxJob, $faxData);
                break;
                
            default:
                Log::info('Unhandled fax event type', [
                    'event_type' => $eventType,
                    'fax_job_id' => $faxJob->id
                ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle fax sending event
     */
    protected function handleFaxSending(FaxJob $faxJob, array $faxData)
    {
        // Update status to sending if not already
        if (!$faxJob->is_sending) {
            $faxJob->markSendingStarted();
        }

        $faxJob->update([
            'telnyx_status' => $faxData['status'] ?? 'sending',
            'delivery_details' => json_encode($faxData)
        ]);

        Log::info('Fax sending status updated', [
            'fax_job_id' => $faxJob->id,
            'status' => $faxData['status'] ?? 'sending'
        ]);
    }

    /**
     * Handle fax delivered event
     */
    protected function handleFaxDelivered(FaxJob $faxJob, array $faxData)
    {
        // Mark as delivered
        $faxJob->markDelivered($faxData['status'] ?? 'delivered', json_encode($faxData));

        Log::info('Fax delivery confirmed', [
            'fax_job_id' => $faxJob->id,
            'telnyx_status' => $faxData['status'] ?? 'delivered',
            'delivered_at' => $faxJob->delivered_at
        ]);

        // Send confirmation email if not already sent
        if (!$faxJob->email_sent) {
            $this->sendConfirmationEmail($faxJob);
        }
    }

    /**
     * Handle fax failed event
     */
    protected function handleFaxFailed(FaxJob $faxJob, array $faxData)
    {
        $failureReason = $faxData['failure_reason'] ?? 'Unknown failure';
        
        $faxJob->update([
            'status' => FaxJob::STATUS_FAILED,
            'telnyx_status' => $faxData['status'] ?? 'failed',
            'error_message' => $failureReason,
            'delivery_details' => json_encode($faxData)
        ]);

        Log::error('Fax delivery failed', [
            'fax_job_id' => $faxJob->id,
            'failure_reason' => $failureReason,
            'fax_data' => $faxData
        ]);

        // TODO: Send failure notification email
    }

    /**
     * Handle fax media processed event
     */
    protected function handleFaxMediaProcessed(FaxJob $faxJob, array $faxData)
    {
        Log::info('Fax media processed', [
            'fax_job_id' => $faxJob->id,
            'media_status' => $faxData['status'] ?? 'processed'
        ]);

        $faxJob->update([
            'delivery_details' => json_encode($faxData)
        ]);
    }

    /**
     * Send confirmation email
     */
    protected function sendConfirmationEmail(FaxJob $faxJob)
    {
        try {
            $faxJob->markEmailSent();
            
            // TODO: Implement actual email sending
            Log::info('Confirmation email marked as sent via webhook', [
                'fax_job_id' => $faxJob->id,
                'recipient_email' => $faxJob->sender_email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark email as sent via webhook', [
                'fax_job_id' => $faxJob->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
