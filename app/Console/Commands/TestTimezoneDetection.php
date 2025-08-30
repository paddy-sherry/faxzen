<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TimezoneService;

class TestTimezoneDetection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fax:test-timezone {phone? : Phone number to test (e.g. +15551234567)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test timezone detection from phone numbers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phoneNumber = $this->argument('phone');
        
        if ($phoneNumber) {
            // Test specific phone number
            $this->testSingleNumber($phoneNumber);
        } else {
            // Test multiple example numbers
            $this->testExampleNumbers();
        }
    }

    /**
     * Test a single phone number
     */
    protected function testSingleNumber($phoneNumber)
    {
        $this->info("Testing phone number: {$phoneNumber}");
        $this->line("─────────────────────────────────────");
        
        try {
            $timezone = TimezoneService::detectTimezoneFromPhoneNumber($phoneNumber);
            $businessInfo = TimezoneService::getBusinessHoursInfo($timezone);
            
            $this->line("📍 Detected Timezone: <fg=green>{$timezone}</>");
            $this->line("🕐 Current Time: <fg=cyan>{$businessInfo['current_time']}</>");
            
            if ($businessInfo['is_business_hours']) {
                $this->line("💼 Business Hours: <fg=green>✅ Yes</>");
            } else {
                $this->line("💼 Business Hours: <fg=red>❌ No</>");
            }
            
            if ($businessInfo['is_weekend']) {
                $this->line("📅 Weekend: <fg=yellow>🏖️ Yes</>");
            } else {
                $this->line("📅 Weekend: <fg=green>📊 No</>");
            }
            
            if (!$businessInfo['is_business_hours']) {
                $this->line("⏰ Next Business Hour: <fg=yellow>{$businessInfo['next_business_hour']}</>");
                $delayHours = round($businessInfo['delay_until_business'] / 3600, 1);
                $this->line("⏳ Delay Until Business: <fg=magenta>{$delayHours} hours</>");
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    /**
     * Test multiple example numbers
     */
    protected function testExampleNumbers()
    {
        $testNumbers = [
            // US Numbers
            '+12125551234' => 'New York (Eastern Time)',
            '+13105551234' => 'Los Angeles (Pacific Time)',
            '+17735551234' => 'Chicago (Central Time)', 
            '+13035551234' => 'Denver (Mountain Time)',
            
            // International Numbers
            '+442012345678' => 'London, UK',
            '+33123456789' => 'Paris, France',
            '+4915123456789' => 'Berlin, Germany',
            '+81312345678' => 'Tokyo, Japan',
            '+61212345678' => 'Sydney, Australia',
            '+861012345678' => 'Beijing, China',
            '+551123456789' => 'São Paulo, Brazil',
            '+5255123456789' => 'Mexico City, Mexico',
        ];

        $this->info("🌍 Testing Geographic Timezone Detection");
        $this->line("═══════════════════════════════════════════════════════════════");
        $this->newLine();

        foreach ($testNumbers as $phone => $description) {
            try {
                $timezone = TimezoneService::detectTimezoneFromPhoneNumber($phone);
                $businessInfo = TimezoneService::getBusinessHoursInfo($timezone);
                
                $this->line("📞 <fg=white>{$phone}</> ({$description})");
                $this->line("   📍 Timezone: <fg=green>{$timezone}</>");
                $this->line("   🕐 Current: <fg=cyan>" . 
                    \Carbon\Carbon::parse($businessInfo['current_time'])->format('M j, g:i A T') . "</>");
                if ($businessInfo['is_business_hours']) {
                    $this->line("   💼 Business Hours: <fg=green>✅ Open</>");
                } else {
                    $this->line("   💼 Business Hours: <fg=red>❌ Closed</>");
                }
                
                if ($businessInfo['is_weekend']) {
                    $this->line("   📅 Status: <fg=yellow>🏖️ Weekend</>");
                } elseif (!$businessInfo['is_business_hours']) {
                    $this->line("   📅 Status: <fg=yellow>🌙 After Hours</>");
                } else {
                    $this->line("   📅 Status: <fg=green>📊 Business Hours</>");
                }
                
                $this->newLine();
                
            } catch (\Exception $e) {
                $this->line("📞 <fg=white>{$phone}</> ({$description})");
                $this->line("   <fg=red>❌ Error: " . $e->getMessage() . "</>");
                $this->newLine();
            }
        }
        
        $this->info("💡 Smart Retry System:");
        $this->line("• <fg=green>Stage 1:</> Quick retries (attempts 2-6) - 2, 4, 6, 8, 10 minutes");
        $this->line("• <fg=yellow>Stage 2:</> Geographic awareness (attempts 7+) - waits for business hours");
        $this->line("• <fg=blue>Business hours:</> 8 AM - 6 PM in recipient's local timezone");
        $this->line("• <fg=magenta>Weekends:</> Wait until Monday 8 AM for persistent busy lines");
        $this->newLine();
        $this->line("💡 Tips:");
        $this->line("• Use 'php artisan fax:test-timezone +15551234567' to test a specific number");
        $this->line("• Most busy lines resolve during quick retry stage (first 30 minutes)");
        $this->line("• Geographic awareness only applies to persistent busy lines");
    }
}