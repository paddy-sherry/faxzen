<?php

namespace App\Http\Controllers;

use App\Models\FaxJob;
use App\Jobs\SendFaxJob;
use Illuminate\Http\Request;

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
} 