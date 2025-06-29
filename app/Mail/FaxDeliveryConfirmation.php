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
            subject: '✅ FaxZen Delivery Confirmation. #'.$this->faxJob->id,
            from: config('mail.from.address', 'noreply@faxzen.com'),
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
        return [];
    }
}
