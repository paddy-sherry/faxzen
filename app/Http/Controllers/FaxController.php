<?php

namespace App\Http\Controllers;

use App\Jobs\SendFaxJob;
use App\Models\FaxJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class FaxController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function step1()
    {
        return view('fax.step1');
    }

    public function processStep1(Request $request)
    {
        $request->validate([
            'pdf_file' => [
                'required',
                File::types(['pdf'])
                    ->max(50 * 1024) // 50MB in KB
            ],
            'recipient_number' => [
                'required',
                'string',
                'regex:/^\+?[1-9]\d{1,14}$/' // Basic international phone number format
            ],
        ]);

        // Store the uploaded file on R2
        $file = $request->file('pdf_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('fax_documents', $filename, 'r2');

        // Create fax job record
        $faxJob = FaxJob::create([
            'recipient_number' => $request->recipient_number,
            'file_path' => $filePath,
            'file_original_name' => $file->getClientOriginalName(),
            'status' => FaxJob::STATUS_PENDING,
            'sender_name' => '',
            'sender_email' => '',
        ]);

        return redirect()->route('fax.step2', $faxJob->id);
    }

    public function step2(FaxJob $faxJob)
    {
        if ($faxJob->status !== FaxJob::STATUS_PENDING) {
            return redirect()->route('fax.step1')->with('error', 'Invalid fax job status.');
        }

        return view('fax.step2', compact('faxJob'));
    }

    public function processStep2(Request $request, FaxJob $faxJob)
    {
        $request->validate([
            'sender_name' => 'required|string|max:255',
            'sender_email' => 'required|email:rfc,dns|max:255',
        ]);

        if ($faxJob->status !== FaxJob::STATUS_PENDING) {
            return redirect()->route('fax.step1')->with('error', 'Invalid fax job status.');
        }

        // Update the fax job with sender details
        $faxJob->update([
            'sender_name' => $request->sender_name,
            'sender_email' => $request->sender_email,
            'status' => FaxJob::STATUS_PAYMENT_PENDING,
        ]);

        // Create Stripe checkout session
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'customer_email' => $faxJob->sender_email,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'FaxZen.com',
                        'description' => "Send fax to {$faxJob->recipient_number}",
                    ],
                    'unit_amount' => $faxJob->amount * 100, // Convert to cents
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('fax.payment.success', $faxJob->id) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('fax.step2', $faxJob->id),
            'metadata' => [
                'fax_job_id' => $faxJob->id,
            ],
        ]);

        // Store the payment intent ID
        $faxJob->update([
            'payment_intent_id' => $checkoutSession->id,
        ]);

        return redirect($checkoutSession->url);
    }

    public function paymentSuccess(FaxJob $faxJob, Request $request)
    {
        $sessionId = $request->get('session_id');
        
        if (!$sessionId) {
            return redirect()->route('fax.step1')->with('error', 'Invalid payment session.');
        }

        try {
            // Retrieve the checkout session to verify payment
            $session = Session::retrieve($sessionId);
            
            if ($session->payment_status === 'paid' && $session->metadata->fax_job_id == $faxJob->id) {
                // Update fax job status to paid
                $faxJob->update([
                    'status' => FaxJob::STATUS_PAID,
                ]);

                // Dispatch the job to send the fax
                SendFaxJob::dispatch($faxJob);

                return view('fax.success', compact('faxJob'));
            }
        } catch (\Exception $e) {
            return redirect()->route('fax.step1')->with('error', 'Payment verification failed.');
        }

        return redirect()->route('fax.step1')->with('error', 'Payment not completed or invalid.');
    }
}
