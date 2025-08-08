<?php

namespace App\Console\Commands;

use App\Models\AccountAccessToken;
use Illuminate\Console\Command;

class CleanupExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:cleanup {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired account access tokens for security';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Looking for expired account access tokens...');
        
        $expiredTokens = AccountAccessToken::where('expires_at', '<', now())->get();
        
        if ($expiredTokens->isEmpty()) {
            $this->info('No expired tokens found.');
            return;
        }
        
        $this->info("Found {$expiredTokens->count()} expired tokens.");
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No tokens will be deleted');
            $this->table(
                ['ID', 'Email', 'Created', 'Expired', 'Used'],
                $expiredTokens->map(function ($token) {
                    return [
                        $token->id,
                        $token->email,
                        $token->created_at->format('M j, Y g:i A'),
                        $token->expires_at->format('M j, Y g:i A'),
                        $token->used_at ? 'Yes' : 'No'
                    ];
                })
            );
            return;
        }
        
        $deletedCount = AccountAccessToken::cleanupExpired();
        
        $this->info("✅ Cleaned up {$deletedCount} expired tokens.");
    }
}