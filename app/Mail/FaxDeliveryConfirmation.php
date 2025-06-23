<?php

namespace App\Mail;

use App\Models\FaxJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

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
            subject: '✅ Fax Delivered Successfully - ' . $this->faxJob->file_original_name,
            from: config('mail.from.address', 'noreply@faxzen.com'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.fax-delivery-confirmation',
            with: [
                'faxJob' => $this->faxJob,
                'deliveredAt' => $this->faxJob->delivered_at->format('M j, Y \a\t g:i A T'),
                'deliveryStatus' => ucwords(str_replace('_', ' ', $this->faxJob->telnyx_status ?? 'delivered')),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
