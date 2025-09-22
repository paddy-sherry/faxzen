<?php

namespace App\Mail;

use App\Models\FaxJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FaxDeliveryConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $faxJob;

    /**
     * Create a new message instance.
     */
    public function __construct(FaxJob $faxJob)
    {
        $this->faxJob = $faxJob;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'FaxZen Delivery Confirmation. #'.$this->faxJob->id,
            from: new Address(config('mail.from.address'), config('mail.from.name')),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Retrieve Stripe payment information
        $paymentDetails = $this->getStripePaymentDetails();
        
        return new Content(
            view: 'emails.fax-delivery-confirmation',
            with: [
                'faxJob' => $this->faxJob,
                'deliveredAt' => $this->faxJob->delivered_at->format('M j, Y \a\t g:i A T'),
                'deliveryStatus' => ucwords(str_replace('_', ' ', $this->faxJob->telnyx_status ?? 'delivered')),
                'paymentDetails' => $paymentDetails,
            ]
        );
    }

    /**
     * Get Stripe payment details for receipt
     */
    private function getStripePaymentDetails(): ?array
    {
        try {
            if (!$this->faxJob->payment_intent_id) {
                return null;
            }

            // Set Stripe API key
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            
            // Retrieve the checkout session
            $session = \Stripe\Checkout\Session::retrieve($this->faxJob->payment_intent_id);
            
            if (!$session || $session->payment_status !== 'paid') {
                return null;
            }

            // Get the payment intent for more details
            $paymentIntent = \Stripe\PaymentIntent::retrieve($session->payment_intent);
            
            // Get the charge for receipt details
            $charge = $paymentIntent->charges->data[0] ?? null;
            
            return [
                'session_id' => $session->id,
                'payment_intent_id' => $paymentIntent->id,
                'amount_total' => $session->amount_total / 100, // Convert from cents
                'amount_subtotal' => $session->amount_subtotal / 100,
                'total_details' => $session->total_details,
                'currency' => strtoupper($session->currency),
                'payment_method_type' => $charge->payment_method_details->type ?? 'card',
                'last4' => $charge->payment_method_details->card->last4 ?? null,
                'brand' => $charge->payment_method_details->card->brand ?? null,
                'receipt_url' => $charge->receipt_url ?? null,
                'created' => $session->created,
                'customer_email' => $session->customer_email,
                'customer_details' => $session->customer_details,
            ];
        } catch (\Exception $e) {
            \Log::warning('Failed to retrieve Stripe payment details for email', [
                'fax_job_id' => $this->faxJob->id,
                'payment_intent_id' => $this->faxJob->payment_intent_id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        try {
            // Check if we have a file path and it's safe to attach
            if (!empty($this->faxJob->file_path) && $this->shouldAttachDocument()) {
                $attachment = $this->createDocumentAttachment();
                if ($attachment) {
                    $attachments[] = $attachment;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to attach document to delivery confirmation email', [
                'fax_job_id' => $this->faxJob->id,
                'file_path' => $this->faxJob->file_path,
                'error' => $e->getMessage()
            ]);
        }

        return $attachments;
    }

    /**
     * Determine if we should attach the document
     */
    private function shouldAttachDocument(): bool
    {
        // Don't attach if file path is empty
        if (empty($this->faxJob->file_path)) {
            return false;
        }

        // For scheduled faxes, the cleanup job protects documents until after delivery
        // so we should try to attach regardless of creation time
        if ($this->faxJob->scheduled_time) {
            // For scheduled faxes, just check if it's a safe file type
            // The cleanup job ensures the file exists until after delivery
            Log::info('Scheduled fax - will attempt attachment regardless of age', [
                'fax_job_id' => $this->faxJob->id,
                'created_at' => $this->faxJob->created_at->toISOString(),
                'scheduled_time' => $this->faxJob->scheduled_time->toISOString(),
                'hours_since_creation' => $this->faxJob->created_at->diffInHours(now())
            ]);
        } else {
            // For immediate faxes, don't attach very old fax jobs (file might be cleaned up)
            if ($this->faxJob->created_at->diffInHours(now()) > 72) {
                Log::info('Immediate fax too old for attachment', [
                    'fax_job_id' => $this->faxJob->id,
                    'hours_since_creation' => $this->faxJob->created_at->diffInHours(now())
                ]);
                return false;
            }
        }

        // Check file extension for safety (only attach common document types)
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'tiff', 'tif'];
        $fileExtension = strtolower(pathinfo($this->faxJob->file_original_name, PATHINFO_EXTENSION));
        
        return in_array($fileExtension, $allowedExtensions);
    }

    /**
     * Create the document attachment
     */
    private function createDocumentAttachment(): ?Attachment
    {
        // Determine storage disk and check file existence
        $disk = null;
        $filePath = $this->faxJob->file_path;

        // Check local storage first
        if (Storage::disk('local')->exists($filePath)) {
            $disk = 'local';
        }
        // Check R2 storage
        elseif (Storage::disk('r2')->exists($filePath)) {
            $disk = 'r2';
        } else {
            Log::info('Document file not found for attachment', [
                'fax_job_id' => $this->faxJob->id,
                'file_path' => $filePath
            ]);
            return null;
        }

        // Check file size (don't attach files larger than 10MB)
        try {
            $fileSize = Storage::disk($disk)->size($filePath);
            if ($fileSize > 10 * 1024 * 1024) { // 10MB limit
                Log::info('Document too large to attach to email', [
                    'fax_job_id' => $this->faxJob->id,
                    'file_size' => $fileSize,
                    'limit' => '10MB'
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::warning('Could not determine file size for attachment', [
                'fax_job_id' => $this->faxJob->id,
                'file_path' => $filePath,
                'disk' => $disk,
                'error' => $e->getMessage()
            ]);
            return null;
        }

        // Create the attachment
        return Attachment::fromStorageDisk($disk, $filePath)
            ->as($this->faxJob->file_original_name)
            ->withMime($this->getMimeType($this->faxJob->file_original_name));
    }

    /**
     * Get MIME type for file extension
     */
    private function getMimeType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        return match($extension) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'tiff', 'tif' => 'image/tiff',
            default => 'application/octet-stream'
        };
    }
}
