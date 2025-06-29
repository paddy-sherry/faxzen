<?php

namespace App\Console\Commands;

use App\Models\FaxJob;
use App\Mail\FaxDeliveryConfirmation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DebugEmail extends Command
{
    protected $signature = 'email:debug {--job-id= : Test email for specific fax job} {--config : Show email configuration} {--test-send : Send test email}';
    protected $description = 'Debug email configuration and test email sending';

    public function handle()
    {
        if ($this->option('config')) {
            $this->showEmailConfig();
            return;
        }

        if ($this->option('test-send')) {
            $this->testEmailSending();
            return;
        }

        $jobId = $this->option('job-id');
        if ($jobId) {
            $this->debugFaxEmail($jobId);
            return;
        }

        $this->error('Please specify an option: --config, --test-send, or --job-id=X');
    }

    protected function showEmailConfig()
    {
        $this->info('📧 EMAIL CONFIGURATION DEBUG');
        $this->newLine();

        // Basic config
        $this->info('📋 BASIC CONFIGURATION:');
        $this->line('  Default Mailer: ' . config('mail.default'));
        $this->line('  From Address: ' . config('mail.from.address'));
        $this->line('  From Name: ' . config('mail.from.name'));
        $this->newLine();

        // Environment variables
        $this->info('🔧 ENVIRONMENT VARIABLES:');
        $mailEnvVars = [
            'MAIL_MAILER',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'MAIL_ENCRYPTION',
            'MAIL_FROM_ADDRESS',
            'MAIL_FROM_NAME',
        ];

        foreach ($mailEnvVars as $var) {
            $value = env($var);
            if ($var === 'MAIL_PASSWORD' && $value) {
                $value = str_repeat('*', strlen($value));
            }
            $this->line("  {$var}: " . ($value ?? 'NOT SET'));
        }
        $this->newLine();

        // Current mailer config
        $currentMailer = config('mail.default');
        $mailerConfig = config("mail.mailers.{$currentMailer}");
        
        $this->info("📮 CURRENT MAILER ({$currentMailer}) CONFIG:");
        foreach ($mailerConfig as $key => $value) {
            if ($key === 'password' && $value) {
                $value = str_repeat('*', strlen($value));
            }
            $this->line("  {$key}: " . (is_array($value) ? json_encode($value) : $value));
        }
        $this->newLine();

        // Test mail configuration
        $this->info('🧪 TESTING MAIL CONFIGURATION:');
        try {
            $transport = Mail::getSymfonyTransport();
            $this->line('  Transport Class: ' . get_class($transport));
            $this->info('  ✅ Mail transport initialized successfully');
        } catch (\Exception $e) {
            $this->error('  ❌ Mail transport failed: ' . $e->getMessage());
        }
    }

    protected function testEmailSending()
    {
        $this->info('📧 TESTING EMAIL SENDING');
        $this->newLine();

        $testEmail = $this->ask('Enter email address to send test to:', 'test@example.com');
        
        try {
            Mail::raw('This is a test email from FaxZen debug command.', function ($message) use ($testEmail) {
                $message->to($testEmail)
                       ->subject('FaxZen Email Test - ' . now()->format('Y-m-d H:i:s'));
            });
            
            $this->info('✅ Test email sent successfully!');
            
            if (config('mail.default') === 'log') {
                $this->warn('📝 Note: Using log driver - check storage/logs/laravel.log for the email content');
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email: ' . $e->getMessage());
            $this->line('Stack trace: ' . $e->getTraceAsString());
        }
    }

    protected function debugFaxEmail($jobId)
    {
        $faxJob = FaxJob::find($jobId);
        if (!$faxJob) {
            $this->error("Fax job {$jobId} not found");
            return;
        }

        $this->info("📧 FAX EMAIL DEBUG - Job #{$jobId}");
        $this->newLine();

        // Job details
        $this->info('📋 FAX JOB DETAILS:');
        $this->line("  ID: {$faxJob->id}");
        $this->line("  Recipient: {$faxJob->recipient_number}");
        $this->line("  Sender Email: {$faxJob->sender_email}");
        $this->line("  Status: {$faxJob->status}");
        $this->line("  Is Delivered: " . ($faxJob->is_delivered ? 'Yes' : 'No'));
        $this->line("  Email Sent: " . ($faxJob->email_sent ? 'Yes' : 'No'));
        $this->line("  Email Sent At: " . ($faxJob->email_sent_at ? $faxJob->email_sent_at->format('Y-m-d H:i:s') : 'Never'));
        $this->newLine();

        // Check if email should be sent
        $shouldSendEmail = $faxJob->is_delivered && !$faxJob->email_sent;
        $this->info('📤 EMAIL SENDING STATUS:');
        $this->line("  Should Send Email: " . ($shouldSendEmail ? 'Yes' : 'No'));
        
        if (!$shouldSendEmail) {
            if (!$faxJob->is_delivered) {
                $this->warn("  Reason: Fax is not marked as delivered yet");
            }
            if ($faxJob->email_sent) {
                $this->warn("  Reason: Email already sent");
            }
        }
        $this->newLine();

        // Test email creation
        $this->info('🧪 TESTING EMAIL CREATION:');
        try {
            $email = new FaxDeliveryConfirmation($faxJob);
            $this->info('  ✅ Email object created successfully');
            
            // Test envelope
            $envelope = $email->envelope();
            $this->line("  Subject: {$envelope->subject}");
            $fromAddress = 'Not set';
            if ($envelope->from && count($envelope->from) > 0) {
                $fromAddress = $envelope->from[0]->address ?? 'Not set';
            }
            $this->line("  From: {$fromAddress}");
            
            // Test content
            $content = $email->content();
            $this->line("  View: {$content->view}");
            $this->line("  Variables: " . implode(', ', array_keys($content->with)));
            
        } catch (\Exception $e) {
            $this->error('  ❌ Failed to create email: ' . $e->getMessage());
            return;
        }
        $this->newLine();

        // Test actual sending
        if ($this->confirm('Do you want to test sending this email?')) {
            $this->info('📧 TESTING EMAIL SENDING:');
            
            try {
                // Enable mail logging temporarily
                $originalDriver = config('mail.default');
                
                if ($this->confirm('Send to log file instead of actual email?', true)) {
                    config(['mail.default' => 'log']);
                }
                
                Mail::to($faxJob->sender_email)->send(new FaxDeliveryConfirmation($faxJob));
                
                $this->info('  ✅ Email sent successfully!');
                
                if (config('mail.default') === 'log') {
                    $this->info('  📝 Check storage/logs/laravel.log for email content');
                }
                
                // Restore original driver
                config(['mail.default' => $originalDriver]);
                
                if ($this->confirm('Mark this fax job as email sent?')) {
                    $faxJob->markEmailSent();
                    $this->info('  ✅ Fax job marked as email sent');
                }
                
            } catch (\Exception $e) {
                $this->error('  ❌ Failed to send email: ' . $e->getMessage());
                $this->line('  Error details: ' . $e->getTraceAsString());
                
                // Log the error for debugging
                Log::error('Email debug command failed to send email', [
                    'fax_job_id' => $faxJob->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->newLine();
        $this->info('💡 RECOMMENDATIONS:');
        
        if (config('mail.default') === 'log') {
            $this->warn('  • Currently using log driver - emails won\'t be sent to recipients');
            $this->line('  • Set MAIL_MAILER in .env to smtp, ses, or another driver for production');
        }
        
        if (!$faxJob->sender_email) {
            $this->error('  • No sender email set for this fax job');
        }
        
        if ($faxJob->is_delivered && !$faxJob->email_sent) {
            $this->warn('  • This fax is delivered but email not sent - run: php artisan fax:check-status --job-id=' . $faxJob->id);
        }
    }
} 