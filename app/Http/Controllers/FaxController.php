<?php

namespace App\Http\Controllers;

use App\Jobs\SendFaxJob;
use App\Models\FaxJob;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
        if ($faxJob->status !== FaxJob::STATUS_PENDING && $faxJob->status !== FaxJob::STATUS_PAYMENT_PENDING) {
            return redirect()->route('fax.step1')->with('error', 'Invalid fax job status.');
        }

        return view('fax.step2', compact('faxJob'));
    }

    public function processStep2(Request $request, FaxJob $faxJob)
    {
        // Check if user is authenticated and has credits
        if (auth()->check() && auth()->user()->hasCredits()) {
            // User has credits - process fax immediately without payment
            // No need to validate sender_email as we use the authenticated user's email

            if ($faxJob->status !== FaxJob::STATUS_PENDING && $faxJob->status !== FaxJob::STATUS_PAYMENT_PENDING) {
                return redirect()->route('fax.step1')->with('error', 'Invalid fax job status.');
            }

            // Update fax job with sender email (use authenticated user's email)
            $faxJob->update([
                'sender_email' => auth()->user()->email,
                'status' => FaxJob::STATUS_PAID,
                'prepared_at' => now(),
                'amount' => 0, // Mark as credit usage
            ]);

            // Deduct one credit from user
            auth()->user()->deductCredit();

            // Dispatch the fax job
            \App\Jobs\SendFaxJob::dispatch($faxJob);

            Log::info('Fax sent using user credits', [
                'fax_job_id' => $faxJob->id,
                'user_id' => auth()->id(),
                'remaining_credits' => auth()->user()->fresh()->fax_credits
            ]);

            return redirect()->route('fax.status', $faxJob->hash);
        }

        // User not authenticated or no credits - proceed with payment flow
        if (auth()->check()) {
            // Authenticated user without credits - no need to validate email
            $request->validate([
                'payment_type' => 'required|in:onetime,credits',
            ]);
            $senderEmail = auth()->user()->email;
        } else {
            // Guest user - validate email
            $request->validate([
                'sender_email' => 'required|email:rfc,dns|max:255',
                'payment_type' => 'required|in:onetime,credits',
            ]);
            $senderEmail = $request->sender_email;
        }

        if ($faxJob->status !== FaxJob::STATUS_PENDING && $faxJob->status !== FaxJob::STATUS_PAYMENT_PENDING) {
            return redirect()->route('fax.step1')->with('error', 'Invalid fax job status.');
        }

        $paymentType = $request->payment_type;
        $isCreditsPackage = $paymentType === 'credits';

        // Update the fax job with sender details and payment type
        $faxJob->update([
            'sender_email' => $senderEmail,
            'status' => FaxJob::STATUS_PAYMENT_PENDING,
        ]);

        // Determine pricing and product details
        if ($isCreditsPackage) {
            $amount = 2000; // $20.00 in cents
            //$amount = 500; // $20.00 in cents
            $productName = 'FaxZen.com - 20 Fax Credits Package';
            $productDescription = "20 fax credits for your account\nFirst fax: {$faxJob->file_original_name} to {$faxJob->recipient_number}";
            $submitMessage = 'Your account will be created with 20 fax credits, and your first fax will be sent immediately.';
        } else {
            $amount = $faxJob->amount * 100; // $5.00 in cents
            $productName = 'FaxZen.com - Single Fax Delivery';
            $productDescription = "Fax delivery to {$faxJob->recipient_number}\nDocument: {$faxJob->file_original_name}";
            $submitMessage = 'Your fax will be sent immediately after payment confirmation.';
        }

        // Create Stripe checkout session
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'customer_email' => $senderEmail,
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
                    'message' => $submitMessage,
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
                        'name' => $productName,
                        'description' => $productDescription,
                        'images' => [
                            'https://imagedelivery.net/k0P4EcPiouU_XzyGSmgmUw/f022f0ec-15f5-465d-ab48-764bd2a96100/public',
                        ],
                    ],
                    'unit_amount' => $amount,
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
                'sender_email' => $senderEmail,
                'document_name' => $faxJob->file_original_name,
                'service' => 'fax_transmission',
                'payment_type' => $paymentType,
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
            

            
            if ($session->payment_status === 'paid' && (string)$session->metadata->fax_job_id === (string)$faxJob->id) {
                $paymentType = $session->metadata->payment_type ?? 'onetime';
                $isCreditsPackage = $paymentType === 'credits';
                

                
                if ($isCreditsPackage) {
                    // Create or find user account for credits package
                    $user = \App\Models\User::where('email', $faxJob->sender_email)->first();
                    
                    if (!$user) {
                        // Create new passwordless user account
                        $user = \App\Models\User::create([
                            'name' => explode('@', $faxJob->sender_email)[0], // Use email prefix as default name
                            'email' => $faxJob->sender_email,
                            'fax_credits' => 20,
                            'credits_purchased_at' => now(),
                            'stripe_customer_id' => $session->customer,
                        ]);
                        Log::info('Created new user account for credits package', ['user_id' => $user->id, 'email' => $user->email, 'initial_credits' => $user->fax_credits]);
                    } else {
                        // Add credits to existing account
                        $beforeCredits = $user->fax_credits;
                        $user->addCredits(20);
                        if ($user->stripe_customer_id === null) {
                            $user->update(['stripe_customer_id' => $session->customer]);
                        }
                        Log::info('Added credits to existing user', ['user_id' => $user->id, 'credits_added' => 20, 'total_credits' => $user->fresh()->fax_credits]);
                    }
                    
                    // Deduct one credit for this fax
                    $user->deductCredit();
                }

                // Update fax job status to paid and mark as prepared
                $faxJob->update([
                    'status' => FaxJob::STATUS_PAID,
                    'prepared_at' => now(),
                ]);

                // Dispatch the job to send the fax
                SendFaxJob::dispatch($faxJob);
                
                Log::info('Payment processed successfully', [
                    'fax_job_id' => $faxJob->id,
                    'payment_type' => $paymentType,
                    'amount' => $isCreditsPackage ? '$20.00 (20 credits)' : '$5.00 (one-time)'
                ]);

                // Redirect to status page instead of success page
                return redirect()->route('fax.status', $faxJob->hash);
            } else {
                Log::warning('Payment verification failed', [
                    'payment_status' => $session->payment_status,
                    'session_fax_job_id' => $session->metadata->fax_job_id ?? 'not_set',
                    'actual_fax_job_id' => $faxJob->id,
                    'session_id' => $sessionId
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Payment verification exception', [
                'error' => $e->getMessage(),
                'fax_job_id' => $faxJob->id,
                'session_id' => $sessionId
            ]);
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
