<?php

namespace App\Http\Controllers;

use App\Jobs\SendFaxJob;
use App\Models\FaxJob;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Rules\ValidFaxNumber;

class FaxController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function step1(Request $request)
    {
        // Handle GET request - show the form
        if ($request->isMethod('GET')) {
            // Get the latest 2 published blog posts
            $latestPosts = Post::published()
                ->orderBy('published_at', 'desc')
                ->take(2)
                ->get();
            
            return view('fax.step1', compact('latestPosts'));
        }

        // Handle POST request - process the form
        $request->validate([
            'country_code' => 'required|string',
            'recipient_number' => [
                'required',
                'string',
                new ValidFaxNumber($request->country_code)
            ],
            'pdf_file' => [
                'required',
                File::types(['pdf', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])
                    ->max(50 * 1024) // 50MB in KB
            ],
        ]);

        // Combine country code and recipient number into E164 format
        $countryCode = $request->country_code;
        $recipientNumber = $request->recipient_number;
        
        // Remove any non-numeric characters from recipient number
        $recipientNumber = preg_replace('/[^0-9]/', '', $recipientNumber);
        
        // Create full phone number in E164 format
        $fullPhoneNumber = $countryCode . $recipientNumber;

        $file = $request->file('pdf_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        
        // Store locally first for fast upload response
        $localPath = $file->storeAs('temp_fax_documents', $filename, 'local');
        
        // Get file size from local storage
        $originalSize = Storage::disk('local')->size($localPath);

        // Create fax job record - file will be moved to R2 in SendFaxJob
        $faxJob = FaxJob::create([
            'recipient_number' => $fullPhoneNumber,
            'file_path' => $localPath, // Initially stored locally
            'file_original_name' => $file->getClientOriginalName(),
            'amount' => config('services.faxzen.price'),
            'status' => FaxJob::STATUS_PENDING,
            'sender_email' => '',

            'original_file_size' => $originalSize,
        ]);

        return redirect()->route('fax.step2', $faxJob->hash);
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
            'sender_email' => 'required|email:rfc,dns|max:255',
        ]);

        if ($faxJob->status !== FaxJob::STATUS_PENDING) {
            return redirect()->route('fax.step1')->with('error', 'Invalid fax job status.');
        }

        // Update the fax job with sender details
        $faxJob->update([
            'sender_email' => $request->sender_email,
            'status' => FaxJob::STATUS_PAYMENT_PENDING,
        ]);

        // Create Stripe checkout session
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'customer_email' => $faxJob->sender_email,
            'billing_address_collection' => 'required',            
            'automatic_tax' => [
                'enabled' => true,
            ],
            'tax_id_collection' => [
                'enabled' => true,
            ],
            'consent_collection' => [
                'terms_of_service' => 'required',
            ],
            'custom_text' => [
                'submit' => [
                    'message' => 'Your fax will be sent immediately after payment confirmation.',
                ],
                'terms_of_service_acceptance' => [
                    'message' => 'By completing this purchase, you agree to our terms of service and privacy policy.',
                ],
            ],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'tax_behavior' => 'exclusive',
                    'product_data' => [
                        'name' => 'FaxZen.com',
                        'description' => "Fax delivery to {$faxJob->recipient_number}\nDocument: {$faxJob->file_original_name}",
                        'images' => [
                            'https://imagedelivery.net/k0P4EcPiouU_XzyGSmgmUw/f022f0ec-15f5-465d-ab48-764bd2a96100/public', // Professional fax/document icon - replace with your logo
                        ],
                    ],
                    'unit_amount' => $faxJob->amount * 100, // Convert to cents
                ],
                'quantity' => 1,
            ]],
            'payment_intent_data' => [
                'statement_descriptor' => 'FAXZEN',
                'statement_descriptor_suffix' => 'FAX',
            ],
            'invoice_creation' => [
                'enabled' => true,
                'invoice_data' => [
                    'description' => "Fax transmission service for document: {$faxJob->file_original_name}",
                    'footer' => 'Thank you for using FaxZen.com - Professional fax services made simple.',
                    'metadata' => [
                        'service' => 'fax_transmission',
                        'recipient' => $faxJob->recipient_number,
                        'document' => $faxJob->file_original_name,
                    ],
                ],
            ],
            'success_url' => route('fax.payment.success', $faxJob->hash) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('fax.step2', $faxJob->hash),
            'metadata' => [
                'fax_job_id' => $faxJob->id,
                'recipient_number' => $faxJob->recipient_number,
                'sender_email' => $faxJob->sender_email,
                'document_name' => $faxJob->file_original_name,
                'service' => 'fax_transmission',
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
                // Update fax job status to paid and mark as prepared
                $faxJob->update([
                    'status' => FaxJob::STATUS_PAID,
                    'prepared_at' => now(),
                ]);

                // Dispatch the job to send the fax
                SendFaxJob::dispatch($faxJob);

                // Redirect to status page instead of success page
                return redirect()->route('fax.status', $faxJob->hash);
            }
        } catch (\Exception $e) {
            return redirect()->route('fax.step1')->with('error', 'Payment verification failed.');
        }

        return redirect()->route('fax.step1')->with('error', 'Payment not completed or invalid.');
    }

    public function status(FaxJob $faxJob)
    {
        // Only allow access to paid fax jobs
        if ($faxJob->status !== FaxJob::STATUS_PAID && $faxJob->status !== FaxJob::STATUS_SENT) {
            return redirect()->route('fax.step1')->with('error', 'Fax job not found or not paid.');
        }

        return view('fax.status', compact('faxJob'));
    }


}
