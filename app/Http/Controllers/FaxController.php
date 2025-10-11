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
            'sender_email' => 'required|email',
            'pdf_files' => [
                'required',
                'array',
                'min:1',
                'max:10'
            ],
            'pdf_files.*' => [
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

        $files = $request->file('pdf_files');
        $fileCount = count($files);
        
        // Process all files
        $filePaths = [];
        $fileOriginalNames = [];
        $originalFileSizes = [];
        $totalSize = 0;
        
        foreach ($files as $file) {
            $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
            
            // Store locally first for fast upload response
            $localPath = $file->storeAs('temp_fax_documents', $filename, 'local');
            
            // Get file size from local storage
            $fileSize = Storage::disk('local')->size($localPath);
            
            $filePaths[] = $localPath;
            $fileOriginalNames[] = $file->getClientOriginalName();
            $originalFileSizes[] = $fileSize;
            $totalSize += $fileSize;
        }
        
        // For backward compatibility, set the first file as the primary file
        $primaryFilePath = $filePaths[0];
        $primaryOriginalName = $fileOriginalNames[0];

        // Get traffic source data from session (captured by middleware)
        $trafficData = session('traffic_source_data', []);
        
        // Create fax job record - files will be moved to R2 in SendFaxJob
        $faxJobData = [
            'recipient_number' => $fullPhoneNumber,
            'file_path' => $primaryFilePath, // Primary file for backward compatibility
            'file_original_name' => $primaryOriginalName, // Primary file name for backward compatibility
            'file_paths' => json_encode($filePaths), // All file paths as JSON
            'file_original_names' => json_encode($fileOriginalNames), // All original names as JSON
            'file_count' => $fileCount, // Number of files
            'amount' => config('services.faxzen.price'),
            'status' => FaxJob::STATUS_PENDING,
            'sender_email' => $request->sender_email,
            'original_file_size' => $totalSize, // Total size of all files
            'original_file_sizes' => json_encode($originalFileSizes), // Individual file sizes as JSON
        ];

        // Add traffic source tracking data if available
        if (!empty($trafficData)) {
            $faxJobData = array_merge($faxJobData, [
                'traffic_source' => $trafficData['traffic_source'] ?? null,
                'utm_source' => $trafficData['utm_source'] ?? null,
                'utm_medium' => $trafficData['utm_medium'] ?? null,
                'utm_campaign' => $trafficData['utm_campaign'] ?? null,
                'utm_term' => $trafficData['utm_term'] ?? null,
                'utm_content' => $trafficData['utm_content'] ?? null,
                'referrer_url' => $trafficData['referrer_url'] ?? null,
                'tracking_data' => $trafficData,
                'landing_page' => $trafficData['landing_page'] ?? null, // This was already being captured!
            ]);
        }

        $faxJob = FaxJob::create($faxJobData);

        return redirect()->route('fax.step2', $faxJob->hash);
    }

    public function step2(FaxJob $faxJob, Request $request)
    {
        if ($faxJob->status !== FaxJob::STATUS_PENDING && $faxJob->status !== FaxJob::STATUS_PAYMENT_PENDING) {
            return redirect()->route('fax.step1')->with('error', 'Invalid fax job status.');
        }

        // Check for discount code
        $discountCode = $request->get('discount');
        if ($discountCode && $this->isValidDiscountCode($discountCode, $faxJob) && !$faxJob->hasDiscount()) {
            // Apply 50% discount
            $discountAmount = $faxJob->amount * 0.5;
            $faxJob->applyDiscount($discountCode, $discountAmount);
            
            session()->flash('discount_applied', [
                'code' => $discountCode,
                'amount' => $discountAmount,
                'new_total' => $faxJob->getFinalAmount()
            ]);
        }

        return view('fax.step2', compact('faxJob'));
    }

    public function processStep2(Request $request, FaxJob $faxJob)
    {
        // Validate scheduling data first
        $schedulingData = $this->validateAndProcessScheduling($request);
        
        // Check if validation returned a redirect response (error case)
        if ($schedulingData instanceof \Illuminate\Http\RedirectResponse) {
            return $schedulingData;
        }
        
        // Check if user is authenticated and has credits
        if (auth()->check() && auth()->user()->hasCredits()) {
            // User has credits - process fax immediately without payment
            // No need to validate sender_email as we use the authenticated user's email
            
            // Validate cover page fields for authenticated users with credits
            $request->validate([
                'include_cover_page' => 'nullable|boolean',
                'cover_sender_name' => 'nullable|string|max:255',
                'cover_sender_company' => 'nullable|string|max:255',
                'cover_sender_phone' => 'nullable|string|max:255',
                'cover_recipient_name' => 'nullable|string|max:255',
                'cover_recipient_company' => 'nullable|string|max:255',
                'cover_subject' => 'nullable|string|max:255',
                'cover_message' => 'nullable|string|max:1000',
            ]);

            if ($faxJob->status !== FaxJob::STATUS_PENDING && $faxJob->status !== FaxJob::STATUS_PAYMENT_PENDING) {
                return redirect()->route('fax.step1')->with('error', 'Invalid fax job status.');
            }

            // Prepare cover page data
            $coverPageData = [];
            if ($request->filled('include_cover_page')) {
                $coverPageData = [
                    'include_cover_page' => true,
                    'cover_sender_name' => $request->cover_sender_name,
                    'cover_sender_company' => $request->cover_sender_company,
                    'cover_sender_phone' => $request->cover_sender_phone,
                    'cover_recipient_name' => $request->cover_recipient_name,
                    'cover_recipient_company' => $request->cover_recipient_company,
                    'cover_subject' => $request->cover_subject,
                    'cover_message' => $request->cover_message,
                ];
            }

            // Update fax job with scheduling and cover page data (email already set in step 1)
            $faxJob->update(array_merge([
                'scheduled_time' => $schedulingData['scheduled_time'],
                'status' => FaxJob::STATUS_PAID,
                'prepared_at' => now(),
                'amount' => 0, // Mark as credit usage
            ], $coverPageData));

            // Deduct one credit from user
            auth()->user()->deductCredit();

            // Dispatch the fax job with scheduling
            $this->dispatchFaxJob($faxJob, $schedulingData);

            Log::info('Fax sent using user credits', [
                'fax_job_id' => $faxJob->id,
                'user_id' => auth()->id(),
                'remaining_credits' => auth()->user()->fresh()->fax_credits
            ]);

            return redirect()->route('fax.status', $faxJob->hash);
        }

        // User not authenticated or no credits - proceed with payment flow
        // Email already collected in step 1, just validate other fields
        $request->validate([
            'payment_type' => 'required|in:onetime,credits,credits_10',
            'include_cover_page' => 'nullable|boolean',
            'cover_sender_name' => 'nullable|string|max:255',
            'cover_sender_company' => 'nullable|string|max:255',
            'cover_sender_phone' => 'nullable|string|max:255',
            'cover_recipient_name' => 'nullable|string|max:255',
            'cover_recipient_company' => 'nullable|string|max:255',
            'cover_subject' => 'nullable|string|max:255',
            'cover_message' => 'nullable|string|max:1000',
        ]);

        if ($faxJob->status !== FaxJob::STATUS_PENDING && $faxJob->status !== FaxJob::STATUS_PAYMENT_PENDING) {
            return redirect()->route('fax.step1')->with('error', 'Invalid fax job status.');
        }

        $paymentType = $request->payment_type;
        $isCreditsPackage = in_array($paymentType, ['credits', 'credits_10']);

        // Prepare cover page data
        $coverPageData = [];
        if ($request->filled('include_cover_page')) {
            $coverPageData = [
                'include_cover_page' => true,
                'cover_sender_name' => $request->cover_sender_name,
                'cover_sender_company' => $request->cover_sender_company,
                'cover_sender_phone' => $request->cover_sender_phone,
                'cover_recipient_name' => $request->cover_recipient_name,
                'cover_recipient_company' => $request->cover_recipient_company,
                'cover_subject' => $request->cover_subject,
                'cover_message' => $request->cover_message,
            ];
        }

        // Update the fax job with scheduling, payment type, and cover page data (email already set in step 1)
        $faxJob->update(array_merge([
            'scheduled_time' => $schedulingData['scheduled_time'],
            'status' => FaxJob::STATUS_PAYMENT_PENDING,
        ], $coverPageData));

        // Determine pricing and product details
        if ($paymentType === 'credits_10') {
            $amount = 1500; // $15.00 in cents
            $credits = 10;
            $productName = 'FaxZen.com - 10 Fax Credits Package';
            $productDescription = "10 fax credits for your account\nFirst fax: {$faxJob->file_original_name} to {$faxJob->recipient_number}";
            $submitMessage = 'Your account will be created with 10 fax credits, and your first fax will be sent immediately.';
        } else {
            $finalAmount = $faxJob->getFinalAmount(); // Use discounted amount if available
            $amount = $finalAmount * 100; // Convert to cents
            $credits = 0; // No credits for one-time payment
            $productName = 'FaxZen.com - Single Fax Delivery';
            
            // Update description to show discount if applied
            if ($faxJob->hasDiscount()) {
                $originalPrice = number_format($faxJob->original_amount ?? $faxJob->amount, 2);
                $discountedPrice = number_format($finalAmount, 2);
                $productDescription = "Fax delivery to {$faxJob->recipient_number}\nDocument: {$faxJob->file_original_name}\nSpecial Offer: \${$originalPrice} → \${$discountedPrice} (50% OFF!)";
            } else {
                $productDescription = "Fax delivery to {$faxJob->recipient_number}\nDocument: {$faxJob->file_original_name}";
            }
            
            $submitMessage = 'Your fax will be sent immediately after payment confirmation.';
        }

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
                'sender_email' => $faxJob->sender_email,
                'document_name' => $faxJob->file_original_name,
                'service' => 'fax_transmission',
                'payment_type' => $paymentType,
                'credits' => $credits,
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
                $credits = (int)($session->metadata->credits ?? 0);
                $isCreditsPackage = in_array($paymentType, ['credits', 'credits_10']);
                

                
                if ($isCreditsPackage) {
                    // Create or find user account for credits package
                    $user = \App\Models\User::where('email', $faxJob->sender_email)->first();
                    
                    if (!$user) {
                        // Create new passwordless user account
                        $user = \App\Models\User::create([
                            'name' => explode('@', $faxJob->sender_email)[0], // Use email prefix as default name
                            'email' => $faxJob->sender_email,
                            'fax_credits' => $credits,
                            'credits_purchased_at' => now(),
                            'stripe_customer_id' => $session->customer,
                        ]);
                        Log::info('Created new user account for credits package', ['user_id' => $user->id, 'email' => $user->email, 'initial_credits' => $user->fax_credits]);
                    } else {
                        // Add credits to existing account
                        $beforeCredits = $user->fax_credits;
                        $user->addCredits($credits);
                        if ($user->stripe_customer_id === null) {
                            $user->update(['stripe_customer_id' => $session->customer]);
                        }
                        Log::info('Added credits to existing user', ['user_id' => $user->id, 'credits_added' => $credits, 'total_credits' => $user->fresh()->fax_credits]);
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
                    'amount' => $isCreditsPackage ? '$' . number_format($session->amount_total / 100, 2) . ' (' . $credits . ' credits)' : '$5.00 (one-time)',
                    'credits' => $credits
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

    public function status($hash)
    {
        // Find fax job by hash (case-insensitive)
        $faxJob = FaxJob::whereRaw('LOWER(hash) = LOWER(?)', [$hash])->first();
        
        if (!$faxJob) {
            return redirect()->route('fax.step1')->with('error', 'Fax job not found.');
        }
        
        // Only allow access to paid fax jobs
        if ($faxJob->status !== FaxJob::STATUS_PAID && $faxJob->status !== FaxJob::STATUS_SENT) {
            return redirect()->route('fax.step1')->with('error', 'Fax job not found or not paid.');
        }

        return view('fax.status', compact('faxJob'));
    }

    /**
     * Validate and process scheduling data from the request
     */
    protected function validateAndProcessScheduling(Request $request)
    {
        $scheduleType = $request->input('schedule_type', 'now');
        $scheduledTime = null;
        
        // Debug logging - remove after fixing
        Log::info('=== SCHEDULING DEBUG - SERVER SIDE ===', [
            'all_inputs' => $request->all(),
            'schedule_type' => $scheduleType,
            'has_scheduled_time_utc' => $request->has('scheduled_time_utc'),
            'scheduled_time_utc_value' => $request->input('scheduled_time_utc'),
            'has_user_timezone' => $request->has('user_timezone'),
            'user_timezone_value' => $request->input('user_timezone'),
        ]);
        
        if ($scheduleType === 'later') {
            // Validate scheduling fields
            $request->validate([
                'schedule_type' => 'required|in:now,later',
                'scheduled_time_utc' => 'nullable|date|after:now',
                'user_timezone' => 'nullable|string|max:255',
            ], [
                'scheduled_time_utc.required' => 'Please select a date and time for scheduling.',
                'scheduled_time_utc.date' => 'Invalid date and time format.',
                'scheduled_time_utc.after' => 'Scheduled time must be in the future.',
            ]);
            
            // Check if JavaScript failed to populate the fields
            if (!$request->has('scheduled_time_utc') || !$request->input('scheduled_time_utc')) {
                return redirect()->back()->withErrors([
                    'schedule_time' => 'Scheduling failed - please ensure JavaScript is enabled and try again, or choose "Send Immediately".'
                ])->withInput();
            }
            
            Log::info('=== SERVER TIMEZONE CONVERSION DEBUG ===', [
                'scheduled_time_utc_raw' => $request->input('scheduled_time_utc'),
                'user_timezone' => $request->input('user_timezone'),
                'server_timezone' => config('app.timezone'),
                'current_server_time' => now()->toISOString(),
            ]);
            
            $scheduledTime = \Carbon\Carbon::parse($request->scheduled_time_utc);
            
            Log::info('=== PARSED SCHEDULED TIME ===', [
                'parsed_time_utc' => $scheduledTime->toISOString(),
                'parsed_time_local' => $scheduledTime->format('Y-m-d H:i:s T'),
                'timezone_offset' => $scheduledTime->format('P'),
                'is_utc' => $scheduledTime->getTimezone()->getName() === 'UTC',
            ]);
            
            // Additional validation: not more than 30 days in advance
            if ($scheduledTime->isAfter(now()->addDays(30))) {
                return redirect()->back()->withErrors([
                    'schedule_time' => 'Cannot schedule more than 30 days in advance.'
                ])->withInput();
            }
            
            // Log scheduling info
            Log::info('Fax scheduled for future delivery', [
                'scheduled_time_utc' => $scheduledTime->toISOString(),
                'user_timezone' => $request->user_timezone,
                'local_time' => $scheduledTime->setTimezone($request->user_timezone)->format('Y-m-d H:i:s T')
            ]);
        }
        
        return [
            'schedule_type' => $scheduleType,
            'scheduled_time' => $scheduledTime,
            'user_timezone' => $request->input('user_timezone'),
        ];
    }

    /**
     * Dispatch the fax job with proper scheduling
     */
    protected function dispatchFaxJob(FaxJob $faxJob, array $schedulingData)
    {
        if ($schedulingData['schedule_type'] === 'later' && $schedulingData['scheduled_time']) {
            // Schedule the job for later execution
            \App\Jobs\SendFaxJob::dispatch($faxJob)->delay($schedulingData['scheduled_time']);
            
            Log::info('Fax job scheduled for delayed execution', [
                'fax_job_id' => $faxJob->id,
                'scheduled_time' => $schedulingData['scheduled_time']->toISOString(),
                'delay_seconds' => $schedulingData['scheduled_time']->diffInSeconds(now())
            ]);
        } else {
            // Send immediately
            \App\Jobs\SendFaxJob::dispatch($faxJob);
            
            Log::info('Fax job dispatched for immediate execution', [
                'fax_job_id' => $faxJob->id
            ]);
        }
    }

    /**
     * Validate discount code for a specific fax job
     */
    private function isValidDiscountCode($discountCode, FaxJob $faxJob)
    {
        // Check if it's the correct format: SAVE50_XXXXXXXX
        if (!str_starts_with($discountCode, 'SAVE50_')) {
            return false;
        }
        
        // Extract the hash portion and verify it matches the fax job
        $hashPortion = substr($discountCode, 7); // Remove 'SAVE50_' prefix
        $expectedHash = strtoupper(substr($faxJob->hash, 0, 8));
        
        return $hashPortion === $expectedHash;
    }
}
