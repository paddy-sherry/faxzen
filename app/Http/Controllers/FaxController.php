<?php

namespace App\Http\Controllers;

use App\Jobs\SendFaxJob;
use App\Models\FaxJob;
use App\Services\KrakenCompressionService;
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

    public function step1(Request $request)
    {
        // Handle GET request - show the form
        if ($request->isMethod('GET')) {
            return view('fax.step1');
        }

        // Handle POST request - process the form
        $request->validate([
            'country_code' => 'required|string',
            'recipient_number' => 'required|string|min:7|max:15',
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
        $filePath = $file->storeAs('fax_documents', $filename, 'r2');

        // Try to compress the file using Kraken.io (supports PDF and images)
        $compressionService = new KrakenCompressionService();
        $compressionResult = $compressionService->compressAndStore($filePath, 'r2');
        
        // Use compressed version if available, otherwise use original
        $finalFilePath = ($compressionResult && $compressionResult['compressed_path']) 
            ? $compressionResult['compressed_path'] 
            : $filePath;

        // Prepare compression data for storage
        $compressionData = [];
        if ($compressionResult) {
            $compressionData = [
                'is_compressed' => $compressionResult['is_compressed'],
                'original_file_size' => $compressionResult['original_size'],
                'compressed_file_size' => $compressionResult['compressed_size'],
                'compression_ratio' => $compressionResult['compression_ratio'],
            ];
        } else {
            // If compression failed, get original file size
            $originalSize = Storage::disk('r2')->size($filePath);
            $compressionData = [
                'is_compressed' => false,
                'original_file_size' => $originalSize,
                'compressed_file_size' => $originalSize,
                'compression_ratio' => 0,
            ];
        }

        // Create fax job record
        $faxJob = FaxJob::create(array_merge([
            'recipient_number' => $fullPhoneNumber,
            'file_path' => $finalFilePath,
            'file_original_name' => $file->getClientOriginalName(),
            'amount' => config('services.faxzen.price'),
            'status' => FaxJob::STATUS_PENDING,
            'sender_name' => '',
            'sender_email' => '',
        ], $compressionData));

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
                        'name' => 'Fax Sending Service',
                        'description' => "Fax delivery to {$faxJob->recipient_number}\nDocument: {$faxJob->file_original_name}\nSender: {$faxJob->sender_name}",
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
            'success_url' => route('fax.payment.success', $faxJob->id) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('fax.step2', $faxJob->id),
            'metadata' => [
                'fax_job_id' => $faxJob->id,
                'recipient_number' => $faxJob->recipient_number,
                'sender_name' => $faxJob->sender_name,
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
