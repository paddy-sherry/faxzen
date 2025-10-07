<?php

namespace App\Mail;

use App\Models\FaxJob;
use App\Http\Controllers\EmailTrackingController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class EarlyFaxReminderEmail extends Mailable
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
            subject: '📠 Quick Reminder - Complete Your Fax - ' . $this->faxJob->file_original_name,
            from: new Address(config('mail.from.address'), config('mail.from.name')),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Generate tracking URL with UTM parameters
        $continueUrl = EmailTrackingController::generateTrackingUrl($this->faxJob, 'early-reminder');

        return new Content(
            view: 'emails.early-fax-reminder',
            with: [
                'faxJob' => $this->faxJob,
                'continueUrl' => $continueUrl,
                'hoursAgo' => round(now()->diffInHours($this->faxJob->created_at)),
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
