<?php

namespace App\Mail;

use App\Models\FaxJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class FaxDeliveryFailed extends Mailable
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
            subject: '❌ Fax Delivery Failed - ' . $this->faxJob->file_original_name,
            from: new Address(config('mail.from.address'), config('mail.from.name')),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.fax-delivery-failed',
            with: [
                'faxJob' => $this->faxJob,
                'failureReason' => $this->faxJob->error_message ?? 'Unknown failure reason',
                'failedAt' => $this->faxJob->updated_at->format('M j, Y \a\t g:i A T'),
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
