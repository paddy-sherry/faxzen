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

    public function retryFaxJob(FaxJob $faxJob)
    {
        // Check if the job can be retried
        if (!$faxJob->canRetry()) {
            return redirect()->route('admin.fax-jobs')->with('error', "Fax job #{$faxJob->id} has already used all retry attempts.");
        }

        // Check if job is in a retryable state
        if ($faxJob->status !== FaxJob::STATUS_FAILED) {
            return redirect()->route('admin.fax-jobs')->with('error', "Fax job #{$faxJob->id} is not in a failed state and cannot be retried.");
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

            return redirect()->route('admin.fax-jobs')->with('success', "Fax job #{$faxJob->id} has been queued for retry. It will be processed shortly.");

        } catch (\Exception $e) {
            return redirect()->route('admin.fax-jobs')->with('error', "Failed to retry fax job #{$faxJob->id}: " . $e->getMessage());
        }
    }
} 