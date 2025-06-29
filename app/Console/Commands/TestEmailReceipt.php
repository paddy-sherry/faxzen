<?php

namespace App\Console\Commands;

use App\Models\FaxJob;
use App\Mail\FaxDeliveryConfirmation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailReceipt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-receipt {--job-id= : Test specific fax job} {--create-test : Create a test fax job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test fax delivery email receipts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 FAX EMAIL RECEIPT TESTER');
        $this->newLine();

        if ($this->option('create-test')) {
            $this->createTestFaxJob();
            return;
        }

        $jobId = $this->option('job-id');
        if (!$jobId) {
            // Show recent fax jobs
            $this->showRecentJobs();
            $jobId = $this->ask('Enter fax job ID to test email receipt');
        }

        $faxJob = FaxJob::find($jobId);
        if (!$faxJob) {
            $this->error("Fax job {$jobId} not found");
            return;
        }

        $this->testEmailReceipt($faxJob);
    }

    protected function showRecentJobs()
    {
        $jobs = FaxJob::orderBy('created_at', 'desc')->limit(10)->get();
        
        $this->info('📋 RECENT FAX JOBS:');
        $headers = ['ID', 'Recipient', 'Status', 'Delivered', 'Email Sent', 'Created'];
        $rows = [];

        foreach ($jobs as $job) {
            $rows[] = [
                $job->id,
                substr($job->recipient_number, -4),
                $job->status,
                $job->is_delivered ? '✅' : '❌',
                $job->email_sent ? '✅' : '❌',
                $job->created_at->format('M j H:i')
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();
    }

    protected function testEmailReceipt(FaxJob $faxJob)
    {
        $this->info("🧪 TESTING EMAIL RECEIPT FOR JOB #{$faxJob->id}");
        $this->newLine();

        // Job details
        $this->info('📋 JOB DETAILS:');
        $this->line("  Recipient: {$faxJob->recipient_number}");
        $this->line("  Sender Email: {$faxJob->sender_email}");
        $this->line("  Status: {$faxJob->status}");
        $this->line("  Delivered: " . ($faxJob->is_delivered ? 'Yes' : 'No'));
        $this->line("  Email Sent: " . ($faxJob->email_sent ? 'Yes' : 'No'));
        $this->line("  File: {$faxJob->file_original_name}");
        $this->newLine();

        // Check email configuration
        $this->info('📧 EMAIL CONFIG:');
        $this->line("  Mailer: " . config('mail.default'));
        $this->line("  From: " . config('mail.from.address'));
        $this->newLine();

        // Test email creation
        try {
            $email = new FaxDeliveryConfirmation($faxJob);
            $envelope = $email->envelope();
            
            $this->info('✅ EMAIL CREATION TEST PASSED');
            $this->line("  Subject: {$envelope->subject}");
            $this->newLine();
            
        } catch (\Exception $e) {
            $this->error('❌ EMAIL CREATION FAILED: ' . $e->getMessage());
            return;
        }

        // Ask to send test email
        if ($this->confirm('Send test email?', true)) {
            $testMode = $this->choice(
                'How would you like to send the test?',
                ['log' => 'To log file (safe)', 'email' => 'To actual email address'],
                'log'
            );

            $originalMailer = config('mail.default');
            
            if ($testMode === 'log') {
                config(['mail.default' => 'log']);
                $this->info('📝 Sending to log file...');
            } else {
                $this->info('📧 Sending to actual email...');
            }

            try {
                Mail::to($faxJob->sender_email)->send(new FaxDeliveryConfirmation($faxJob));
                
                $this->info('✅ EMAIL SENT SUCCESSFULLY!');
                
                if ($testMode === 'log') {
                    $this->warn('📝 Check storage/logs/laravel.log for email content');
                } else {
                    $this->info("📧 Email sent to {$faxJob->sender_email}");
                    
                    if ($this->confirm('Mark this job as email sent?')) {
                        $faxJob->markEmailSent();
                        $this->info('✅ Job marked as email sent');
                    }
                }
                
            } catch (\Exception $e) {
                $this->error('❌ EMAIL SENDING FAILED: ' . $e->getMessage());
            } finally {
                // Restore original mailer
                config(['mail.default' => $originalMailer]);
            }
        }

        $this->newLine();
        $this->info('💡 USEFUL COMMANDS:');
        $this->line('  php artisan fax:check-status --send-missing-emails');
        $this->line('  php artisan email:debug --config');
        $this->line('  php artisan fax:debug --list');
    }

    protected function createTestFaxJob()
    {
        $this->info('🧪 CREATING TEST FAX JOB');
        $this->newLine();

        $email = $this->ask('Enter sender email', 'test@example.com');
        $phone = $this->ask('Enter recipient phone', '+1234567890');
        $filename = $this->ask('Enter filename', 'test-document.pdf');

        $faxJob = FaxJob::create([
            'sender_email' => $email,
            'recipient_number' => $phone,
            'file_original_name' => $filename,
            'file_path' => 'test/' . $filename,
            'status' => FaxJob::STATUS_SENT,
            'is_delivered' => true,
            'delivered_at' => now(),
            'telnyx_status' => 'delivered',
            'email_sent' => false,
            'telnyx_fax_id' => 'test-' . time(),
        ]);

        $this->info("✅ Test fax job created with ID: {$faxJob->id}");
        $this->line("  Email: {$faxJob->sender_email}");
        $this->line("  Phone: {$faxJob->recipient_number}");
        $this->line("  Status: Delivered (ready for email test)");
        $this->newLine();

        if ($this->confirm('Test email receipt for this job now?')) {
            $this->testEmailReceipt($faxJob);
        }
    }
}
