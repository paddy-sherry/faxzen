<?php

namespace App\Mail;

use App\Models\FaxJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FaxReminderEmail extends Mailable
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
            subject: '📠 Complete Your Fax - ' . $this->faxJob->file_original_name,
            from: config('mail.from.address', 'noreply@faxzen.com'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.fax-reminder',
            with: [
                'faxJob' => $this->faxJob,
                'continueUrl' => route('fax.step2', $this->faxJob->hash),
                'hoursAgo' => round($this->faxJob->created_at->diffInHours()),
                'fileName' => $this->faxJob->file_original_name,
                'recipientNumber' => $this->faxJob->recipient_number,
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
