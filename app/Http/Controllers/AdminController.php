<?php

namespace App\Http\Controllers;

use App\Models\FaxJob;
use App\Jobs\SendFaxJob;
use App\Mail\FaxDeliveryFailed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Telnyx\Telnyx;
use Telnyx\Fax;

class AdminController extends Controller
{
    public function faxJobs(Request $request)
    {
        $query = FaxJob::query()->orderBy('created_at', 'desc');
        
        // Optional status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        // Optional date filter
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $faxJobs = $query->paginate(50);
        
        return view('admin.fax-jobs', compact('faxJobs'));
    }

    public function retryFaxJob($id, Request $request)
    {
        // Find the fax job
        $faxJob = FaxJob::find($id);
        
        if (!$faxJob) {
            $errorMsg = "Fax job #{$id} not found.";
            
            if ($request->isMethod('GET')) {
                return response()->json(['error' => $errorMsg], 404);
            }
            
            return redirect()->route('admin.fax-jobs')->with('error', $errorMsg);
        }
        
        // Check if the job can be retried
        if (!$faxJob->canRetry()) {
            $errorMsg = "Fax job #{$faxJob->id} has already used all retry attempts.";
            
            // For GET requests, return JSON or simple response
            if ($request->isMethod('GET')) {
                return response()->json(['error' => $errorMsg], 400);
            }
            
            return redirect()->route('admin.fax-jobs')->with('error', $errorMsg);
        }

        // Check if job is in a retryable state
        if ($faxJob->status !== FaxJob::STATUS_FAILED) {
            $errorMsg = "Fax job #{$faxJob->id} is not in a failed state and cannot be retried.";
            
            // For GET requests, return JSON or simple response
            if ($request->isMethod('GET')) {
                return response()->json(['error' => $errorMsg], 400);
            }
            
            return redirect()->route('admin.fax-jobs')->with('error', $errorMsg);
        }

        try {
            // Reset the job status and clear error state
            $faxJob->update([
                'status' => FaxJob::STATUS_PAID,
                'telnyx_status' => null,
                'telnyx_fax_id' => null,
                'error_message' => null,
                'is_sending' => false,
                'is_delivered' => false,
                'retry_attempts' => $faxJob->retry_attempts // Keep current retry count
            ]);

            // Dispatch the SendFaxJob
            SendFaxJob::dispatch($faxJob);

            $successMsg = "Fax job #{$faxJob->id} has been queued for retry. It will be processed shortly.";
            
            // For GET requests, return JSON response
            if ($request->isMethod('GET')) {
                return response()->json([
                    'success' => $successMsg,
                    'job_id' => $faxJob->id,
                    'new_status' => $faxJob->status
                ]);
            }

            return redirect()->route('admin.fax-jobs')->with('success', $successMsg);

        } catch (\Exception $e) {
            $errorMsg = "Failed to retry fax job #{$faxJob->id}: " . $e->getMessage();
            
            // For GET requests, return JSON error
            if ($request->isMethod('GET')) {
                return response()->json(['error' => $errorMsg], 500);
            }
            
            return redirect()->route('admin.fax-jobs')->with('error', $errorMsg);
        }
    }

    public function checkStatus($id, Request $request)
    {
        $faxJob = FaxJob::find($id);
        
        if (!$faxJob) {
            return response()->json(['error' => "Fax job #{$id} not found."], 404);
        }

        if (!$faxJob->telnyx_fax_id) {
            return response()->json(['error' => "Fax job #{$id} doesn't have a Telnyx fax ID."], 400);
        }

        try {
            // Set up Telnyx API
            Telnyx::setApiKey(config('services.telnyx.api_key'));
            \Telnyx\Telnyx::$apiBase = config('services.telnyx.api_base');

            // Retrieve fax status from Telnyx
            $fax = Fax::retrieve($faxJob->telnyx_fax_id);

            // Store old status for comparison
            $oldStatus = $faxJob->telnyx_status;
            $oldDbStatus = $faxJob->status;

            // Update our database with the latest status
            $faxJob->update([
                'telnyx_status' => $fax->status,
                'delivery_details' => json_encode($fax->toArray())
            ]);

            // Handle status changes (reusing logic from CheckFaxStatus command)
            switch ($fax->status) {
                case 'delivered':
                    if (!$faxJob->is_delivered) {
                        $faxJob->markDelivered($fax->status, json_encode($fax->toArray()));
                    }
                    
                    // Send confirmation email if not already sent
                    if (!$faxJob->email_sent) {
                        try {
                            \Mail::to($faxJob->sender_email)->bcc('faxzen.com+656498d49b@invite.trustpilot.com')->send(new \App\Mail\FaxDeliveryConfirmation($faxJob));
                            $faxJob->markEmailSent();
                        } catch (\Exception $e) {
                            Log::error("Failed to send fax confirmation email via manual check", [
                                'fax_job_id' => $faxJob->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    break;

                case 'failed':
                    $failureReason = $fax->failure_reason ?? 'Fax delivery failed';
                    $isRetryableFailure = in_array($failureReason, [
                        'receiver_call_dropped',
                        'sender_call_dropped', 
                        'timeout',
                        'busy'
                    ]);
                    
                    // Check for ECM-related errors
                    $isEcmError = str_contains(strtolower($failureReason), 'ecm') || 
                                  str_contains(strtolower($failureReason), 'error_correction');
                    
                    $faxJob->update([
                        'status' => FaxJob::STATUS_FAILED,
                        'error_message' => $failureReason
                    ]);

                    // Send failure notification email if not already sent
                    if (!$faxJob->failure_email_sent) {
                        try {
                            \Mail::to($faxJob->sender_email)->send(new \App\Mail\FaxDeliveryFailed($faxJob));
                            $faxJob->markFailureEmailSent();
                            
                            Log::info('Failure notification email sent via manual check', [
                                'fax_job_id' => $faxJob->id,
                                'recipient_email' => $faxJob->sender_email,
                                'failure_reason' => $failureReason
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Failed to send failure notification email via manual check", [
                                'fax_job_id' => $faxJob->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    break;

                case 'sending':
                    if (!$faxJob->is_sending) {
                        $faxJob->markSendingStarted();
                    }
                    break;
            }

            // Refresh the model to get updated data
            $faxJob->refresh();

            Log::info("Fax status checked manually", [
                'fax_job_id' => $faxJob->id,
                'telnyx_fax_id' => $faxJob->telnyx_fax_id,
                'old_telnyx_status' => $oldStatus,
                'new_telnyx_status' => $fax->status,
                'old_db_status' => $oldDbStatus,
                'new_db_status' => $faxJob->status
            ]);

            return response()->json([
                'success' => true,
                'message' => "Status updated successfully",
                'fax_job_id' => $faxJob->id,
                'old_status' => $oldStatus,
                'new_status' => $fax->status,
                'updated_data' => [
                    'status' => $faxJob->status,
                    'telnyx_status' => $faxJob->telnyx_status,
                    'is_delivered' => $faxJob->is_delivered,
                    'is_sending' => $faxJob->is_sending,
                    'email_sent' => $faxJob->email_sent,
                    'error_message' => $faxJob->error_message,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to check fax status manually", [
                'fax_job_id' => $faxJob->id,
                'telnyx_fax_id' => $faxJob->telnyx_fax_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => "Failed to check status: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show conversion analytics by traffic source
     */
    public function analytics(Request $request)
    {
        // Default to last 30 days
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Base query for paid conversions (completed fax jobs)
        $baseQuery = FaxJob::whereIn('status', [FaxJob::STATUS_PAID, FaxJob::STATUS_SENT])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        // Overall stats
        $totalConversions = (clone $baseQuery)->count();
        $totalRevenue = (clone $baseQuery)->sum('amount');

        // Conversions by traffic source
        $conversionsBySource = (clone $baseQuery)->select('traffic_source')
            ->selectRaw('COUNT(*) as conversions')
            ->selectRaw('SUM(amount) as revenue')
            ->selectRaw('AVG(amount) as avg_order_value')
            ->groupBy('traffic_source')
            ->orderByRaw('COUNT(*) desc')
            ->get();

        // AdWords specific analytics
        $adwordsConversions = (clone $baseQuery)->where('traffic_source', 'adwords')
            ->select('utm_campaign', 'utm_term')
            ->selectRaw('COUNT(*) as conversions')
            ->selectRaw('SUM(amount) as revenue')
            ->groupBy('utm_campaign', 'utm_term')
            ->orderByRaw('COUNT(*) desc')
            ->get();

        // Organic vs Paid comparison
        $sourceComparison = [
            'adwords' => (clone $baseQuery)->where('traffic_source', 'adwords')->count(),
            'organic' => (clone $baseQuery)->where('traffic_source', 'organic')->count(),
            'direct' => (clone $baseQuery)->where('traffic_source', 'direct')->count(),
            'referral' => (clone $baseQuery)->where('traffic_source', 'referral')->count(),
            'social' => (clone $baseQuery)->where('traffic_source', 'social')->count(),
            'other' => (clone $baseQuery)->whereNotIn('traffic_source', ['adwords', 'organic', 'direct', 'referral', 'social'])
                ->orWhereNull('traffic_source')->count(),
        ];

        // Daily conversion trends
        $dailyTrends = (clone $baseQuery)->select(
                DB::raw('DATE(created_at) as date'),
                'traffic_source'
            )
            ->selectRaw('COUNT(*) as conversions')
            ->groupBy('date', 'traffic_source')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        // Top performing campaigns (AdWords)
        $topCampaigns = (clone $baseQuery)->where('traffic_source', 'adwords')
            ->whereNotNull('utm_campaign')
            ->select('utm_campaign')
            ->selectRaw('COUNT(*) as conversions')
            ->selectRaw('SUM(amount) as revenue')
            ->groupBy('utm_campaign')
            ->orderByRaw('COUNT(*) desc')
            ->limit(10)
            ->get();

        // Top performing keywords (AdWords)
        $topKeywords = (clone $baseQuery)->where('traffic_source', 'adwords')
            ->whereNotNull('utm_term')
            ->select('utm_term')
            ->selectRaw('COUNT(*) as conversions')
            ->selectRaw('SUM(amount) as revenue')
            ->groupBy('utm_term')
            ->orderByRaw('COUNT(*) desc')
            ->limit(10)
            ->get();

        // Daily status breakdown for chart (all fax jobs, not just conversions)
        $dailyStatusData = FaxJob::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->select(
                DB::raw('DATE(created_at) as date'),
                'status'
            )
            ->selectRaw('COUNT(*) as count')
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        return view('admin.analytics', compact(
            'totalConversions',
            'totalRevenue',
            'conversionsBySource', 
            'adwordsConversions',
            'sourceComparison',
            'dailyTrends',
            'topCampaigns',
            'topKeywords',
            'dailyStatusData',
            'dateFrom',
            'dateTo'
        ));
    }
} 