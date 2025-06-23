<?php

namespace App\Console\Commands;

use App\Mail\FaxDeliveryConfirmation;
use App\Models\FaxJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailReceipt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-receipt {fax_job_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the fax delivery confirmation email with Stripe receipt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $faxJobId = $this->argument('fax_job_id');
        
        if ($faxJobId) {
            $faxJob = FaxJob::find($faxJobId);
        } else {
            // Get the most recent delivered fax job
            $faxJob = FaxJob::where('is_delivered', true)
                ->whereNotNull('payment_intent_id')
                ->orderBy('delivered_at', 'desc')
                ->first();
        }
        
        if (!$faxJob) {
            $this->error('No suitable fax job found. Please provide a fax job ID or ensure there are delivered fax jobs.');
            return 1;
        }
        
        $this->info("Testing email for Fax Job ID: {$faxJob->id}");
        $this->info("Payment Intent ID: {$faxJob->payment_intent_id}");
        $this->info("Recipient: {$faxJob->sender_email}");
        
        try {
            // Create the mailable
            $mailable = new FaxDeliveryConfirmation($faxJob);
            
            // Since we're using 'log' driver, this will write to the log file
            Mail::to($faxJob->sender_email)->send($mailable);
            
            $this->info("✅ Email sent successfully!");
            $this->info("Check storage/logs/laravel.log for the email content.");
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
