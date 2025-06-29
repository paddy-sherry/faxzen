<?php

namespace App\Console\Commands;

use App\Models\FaxJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Telnyx\Telnyx;
use Telnyx\Fax;

class DebugFaxStatus extends Command
{
    protected $signature = 'fax:debug {--job-id= : Debug specific fax job ID} {--list : List recent fax jobs} {--hours=24 : Hours to look back}';
    protected $description = 'Debug fax jobs with detailed output and database inspection';

    public function handle()
    {
        if ($this->option('list')) {
            $this->listRecentFaxJobs();
            return;
        }

        $jobId = $this->option('job-id');
        if (!$jobId) {
            $this->error('Please provide a job ID with --job-id=X or use --list to see recent jobs');
            return;
        }

        $this->debugSingleFaxJob($jobId);
    }

    protected function listRecentFaxJobs()
    {
        $hours = $this->option('hours');
        $faxJobs = FaxJob::where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'desc')
            ->get();

        $this->info("Recent fax jobs from last {$hours} hours:");
        $this->newLine();

        if ($faxJobs->isEmpty()) {
            $this->warn('No fax jobs found in the specified time period.');
            return;
        }

        $headers = ['ID', 'Hash', 'Recipient', 'Status', 'Telnyx Status', 'Is Delivered', 'Email Sent', 'Created At'];
        $rows = [];

        foreach ($faxJobs as $job) {
            $rows[] = [
                $job->id,
                substr($job->hash, 0, 8) . '...',
                $job->recipient_number,
                $job->status,
                $job->telnyx_status ?? 'N/A',
                $job->is_delivered ? '✅' : '❌',
                $job->email_sent ? '✅' : '❌',
                $job->created_at->format('Y-m-d H:i:s')
            ];
        }

        $this->table($headers, $rows);
    }

    protected function debugSingleFaxJob($jobId)
    {
        $faxJob = FaxJob::find($jobId);
        if (!$faxJob) {
            $this->error("Fax job {$jobId} not found");
            return;
        }

        $this->info("=== FAX JOB DEBUG REPORT ===");
        $this->newLine();

        // Database Status
        $this->info("📊 DATABASE STATUS:");
        $this->line("  ID: {$faxJob->id}");
        $this->line("  Hash: {$faxJob->hash}");
        $this->line("  Recipient: {$faxJob->recipient_number}");
        $this->line("  Sender Email: {$faxJob->sender_email}");
        $this->line("  Status: {$faxJob->status}");
        $this->line("  Telnyx Fax ID: " . ($faxJob->telnyx_fax_id ?? 'NOT SET'));
        $this->line("  Telnyx Status: " . ($faxJob->telnyx_status ?? 'NOT SET'));
        $this->newLine();

        // Progress Flags
        $this->info("🏁 PROGRESS FLAGS:");
        $this->line("  Is Preparing: " . ($faxJob->is_preparing ? '✅' : '❌'));
        $this->line("  Is Sending: " . ($faxJob->is_sending ? '✅' : '❌'));
        $this->line("  Is Delivered: " . ($faxJob->is_delivered ? '✅' : '❌'));
        $this->line("  Email Sent: " . ($faxJob->email_sent ? '✅' : '❌'));
        $this->newLine();

        // Timestamps
        $this->info("⏰ TIMESTAMPS:");
        $this->line("  Created: " . $faxJob->created_at->format('Y-m-d H:i:s'));
        $this->line("  Prepared: " . ($faxJob->prepared_at ? $faxJob->prepared_at->format('Y-m-d H:i:s') : 'NOT SET'));
        $this->line("  Sending Started: " . ($faxJob->sending_started_at ? $faxJob->sending_started_at->format('Y-m-d H:i:s') : 'NOT SET'));
        $this->line("  Delivered: " . ($faxJob->delivered_at ? $faxJob->delivered_at->format('Y-m-d H:i:s') : 'NOT SET'));
        $this->line("  Email Sent: " . ($faxJob->email_sent_at ? $faxJob->email_sent_at->format('Y-m-d H:i:s') : 'NOT SET'));
        $this->newLine();

        // Current Step
        $this->info("📍 CURRENT PROGRESS:");
        $this->line("  Step: {$faxJob->getCurrentStep()}/4 ({$faxJob->getCurrentStepName()})");
        $this->line("  Completion: {$faxJob->getCompletionPercentage()}%");
        $this->newLine();

        // File Information
        $this->info("📄 FILE INFORMATION:");
        $this->line("  Original Name: " . ($faxJob->file_original_name ?? 'NOT SET'));
        $this->line("  File Path: " . ($faxJob->file_path ?? 'NOT SET'));
        $this->line("  File Size: " . ($faxJob->original_file_size ?? 'NOT SET') . ' bytes');
        
        // Check if file exists
        if ($faxJob->file_path) {
            $fullPath = storage_path('app/' . $faxJob->file_path);
            $fileExists = file_exists($fullPath);
            $this->line("  File Exists: " . ($fileExists ? '✅' : '❌'));
            if ($fileExists) {
                $this->line("  Current Size: " . filesize($fullPath) . ' bytes');
            }
        }
        $this->newLine();

        // Error Information
        if ($faxJob->error_message) {
            $this->error("❌ ERROR MESSAGE:");
            $this->line("  " . $faxJob->error_message);
            $this->newLine();
        }

        // Telnyx API Check
        if ($faxJob->telnyx_fax_id) {
            $this->info("🌐 TELNYX API STATUS:");
            try {
                Telnyx::setApiKey(config('services.telnyx.api_key'));
                \Telnyx\Telnyx::$apiBase = config('services.telnyx.api_base');
                
                $fax = Fax::retrieve($faxJob->telnyx_fax_id);
                
                $this->line("  API Status: {$fax->status}");
                $this->line("  Direction: " . ($fax->direction ?? 'N/A'));
                $this->line("  To: " . ($fax->to ?? 'N/A'));
                $this->line("  From: " . ($fax->from ?? 'N/A'));
                $this->line("  Pages: " . ($fax->page_count ?? 'N/A'));
                $this->line("  Created: " . ($fax->created_at ?? 'N/A'));
                $this->line("  Updated: " . ($fax->updated_at ?? 'N/A'));
                
                if (isset($fax->failure_reason)) {
                    $this->error("  Failure Reason: {$fax->failure_reason}");
                }
                
                // Show full API response if very verbose
                if ($this->getOutput()->isVeryVerbose()) {
                    $this->newLine();
                    $this->info("🔍 FULL TELNYX RESPONSE:");
                    $this->line(json_encode($fax->toArray(), JSON_PRETTY_PRINT));
                }
                
            } catch (\Exception $e) {
                $this->error("  ❌ Failed to retrieve from Telnyx API: " . $e->getMessage());
            }
        } else {
            $this->warn("⚠️  No Telnyx Fax ID - fax may not have been submitted to Telnyx yet");
        }

        $this->newLine();
        
        // Delivery Details
        if ($faxJob->delivery_details) {
            $this->info("📋 DELIVERY DETAILS:");
            $details = json_decode($faxJob->delivery_details, true);
            if ($details) {
                foreach ($details as $key => $value) {
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }
                    $this->line("  {$key}: {$value}");
                }
            } else {
                $this->line("  Invalid JSON in delivery_details");
            }
            $this->newLine();
        }

        // Recommendations
        $this->info("💡 RECOMMENDATIONS:");
        
        if (!$faxJob->telnyx_fax_id) {
            $this->warn("  • Fax has no Telnyx ID - check if SendFaxJob was processed");
        }
        
        if ($faxJob->status === 'sent' && !$faxJob->is_delivered && $faxJob->created_at->diffInHours(now()) > 1) {
            $this->warn("  • Fax has been 'sent' for over 1 hour but not delivered - may need manual check");
        }
        
        if ($faxJob->is_delivered && !$faxJob->email_sent) {
            $this->warn("  • Fax is delivered but confirmation email not sent - check email configuration");
        }
        
        if ($faxJob->file_path && !file_exists(storage_path('app/' . $faxJob->file_path))) {
            $this->error("  • File is missing from storage - this will cause sending to fail");
        }
    }
} 