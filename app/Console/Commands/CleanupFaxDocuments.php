<?php

namespace App\Console\Commands;

use App\Models\FaxJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupFaxDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fax:cleanup-documents {--hours=48 : Delete documents older than N hours} {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up fax documents from local storage and R2 after specified hours (default: 48 hours). Protects scheduled faxes that haven\'t been sent yet.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        $dryRun = $this->option('dry-run');
        
        $this->info("Looking for fax documents older than {$hours} hours to clean up...");

        // Find fax jobs older than specified hours that are safe to clean up
        // Exclude scheduled faxes that haven't been sent yet
        $oldFaxJobs = FaxJob::where('created_at', '<=', now()->subHours($hours))
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->where(function($query) {
                $query->where(function($subQuery) {
                    // Include faxes that are not scheduled (scheduled_time is null)
                    $subQuery->whereNull('scheduled_time');
                })->orWhere(function($subQuery) {
                    // Include scheduled faxes that have already been sent or failed
                    $subQuery->whereNotNull('scheduled_time')
                        ->whereIn('status', [FaxJob::STATUS_SENT, FaxJob::STATUS_FAILED]);
                })->orWhere(function($subQuery) {
                    // Include scheduled faxes where the scheduled time has already passed
                    // and they've been processed (not in pending states)
                    $subQuery->whereNotNull('scheduled_time')
                        ->where('scheduled_time', '<=', now())
                        ->whereNotIn('status', [FaxJob::STATUS_PENDING, FaxJob::STATUS_PAYMENT_PENDING]);
                });
            })
            ->get();

        if ($oldFaxJobs->isEmpty()) {
            $this->info('No old fax documents found to clean up.');
            return;
        }

        $this->info("Found {$oldFaxJobs->count()} old fax jobs with documents to clean up.");

        // Show information about scheduled faxes that are being protected
        $protectedScheduledFaxes = FaxJob::where('created_at', '<=', now()->subHours($hours))
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->whereNotNull('scheduled_time')
            ->where(function($query) {
                $query->where('scheduled_time', '>', now())
                    ->orWhereIn('status', [FaxJob::STATUS_PENDING, FaxJob::STATUS_PAYMENT_PENDING, FaxJob::STATUS_PAID]);
            })
            ->count();

        if ($protectedScheduledFaxes > 0) {
            $this->info("Protected {$protectedScheduledFaxes} scheduled fax documents from cleanup.");
        }

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will be deleted');
            $this->table(
                ['ID', 'File Path', 'Storage', 'Status', 'Scheduled', 'Created', 'Hours Old', 'File Size'],
                $oldFaxJobs->map(function ($job) {
                    $isLocal = str_starts_with($job->file_path, 'temp_fax_documents/');
                    $isR2 = str_starts_with($job->file_path, 'fax_documents/');
                    $storageType = $isLocal ? 'Local' : ($isR2 ? 'R2' : 'Unknown');
                    
                    // Check if file exists and get size
                    $fileExists = false;
                    $fileSize = 'N/A';
                    
                    if ($isLocal && Storage::disk('local')->exists($job->file_path)) {
                        $fileExists = true;
                        $fileSize = $this->formatBytes(Storage::disk('local')->size($job->file_path));
                    } elseif ($isR2 && Storage::disk('r2')->exists($job->file_path)) {
                        $fileExists = true;
                        try {
                            $fileSize = $this->formatBytes(Storage::disk('r2')->size($job->file_path));
                        } catch (\Exception $e) {
                            $fileSize = 'Error';
                        }
                    }
                    
                    $scheduledInfo = $job->scheduled_time 
                        ? ($job->scheduled_time->isPast() ? 'Past' : 'Future (' . $job->scheduled_time->diffForHumans() . ')')
                        : 'No';
                    
                    return [
                        $job->id,
                        strlen($job->file_path) > 30 ? '...' . substr($job->file_path, -30) : $job->file_path,
                        $storageType . ($fileExists ? ' ✓' : ' ✗'),
                        ucfirst($job->status),
                        $scheduledInfo,
                        $job->created_at->format('M j g:i A'),
                        now()->diffInHours($job->created_at),
                        $fileSize
                    ];
                })
            );
            return;
        }

        $deletedLocal = 0;
        $deletedR2 = 0;
        $errorCount = 0;
        $totalSizeFreed = 0;

        foreach ($oldFaxJobs as $faxJob) {
            try {
                $this->line("Processing fax job #{$faxJob->id} ({$faxJob->file_path})...");

                // Final safety check - double-check that this isn't a pending scheduled fax
                if ($faxJob->scheduled_time && $faxJob->scheduled_time->isFuture()) {
                    $this->warn("  ⚠️ Skipping - this is a future scheduled fax ({$faxJob->scheduled_time->diffForHumans()})");
                    continue;
                }

                if ($faxJob->scheduled_time && in_array($faxJob->status, [FaxJob::STATUS_PENDING, FaxJob::STATUS_PAYMENT_PENDING, FaxJob::STATUS_PAID])) {
                    $this->warn("  ⚠️ Skipping - this is a scheduled fax in pending state");
                    continue;
                }

                $fileDeleted = false;

                // Handle local storage files
                if (str_starts_with($faxJob->file_path, 'temp_fax_documents/')) {
                    if (Storage::disk('local')->exists($faxJob->file_path)) {
                        $fileSize = Storage::disk('local')->size($faxJob->file_path);
                        
                        if (Storage::disk('local')->delete($faxJob->file_path)) {
                            $deletedLocal++;
                            $totalSizeFreed += $fileSize;
                            $this->info("  ✅ Deleted from local storage ({$this->formatBytes($fileSize)})");
                            $fileDeleted = true;
                        } else {
                            $this->error("  ❌ Failed to delete from local storage");
                            $errorCount++;
                        }
                    } else {
                        $this->warn("  ⚠️ File not found in local storage");
                    }
                }

                // Handle R2 storage files
                elseif (str_starts_with($faxJob->file_path, 'fax_documents/')) {
                    if (Storage::disk('r2')->exists($faxJob->file_path)) {
                        try {
                            $fileSize = Storage::disk('r2')->size($faxJob->file_path);
                            
                            if (Storage::disk('r2')->delete($faxJob->file_path)) {
                                $deletedR2++;
                                $totalSizeFreed += $fileSize;
                                $this->info("  ✅ Deleted from R2 storage ({$this->formatBytes($fileSize)})");
                                $fileDeleted = true;
                            } else {
                                $this->error("  ❌ Failed to delete from R2 storage");
                                $errorCount++;
                            }
                        } catch (\Exception $e) {
                            $this->error("  ❌ Error accessing R2 file: " . $e->getMessage());
                            $errorCount++;
                        }
                    } else {
                        $this->warn("  ⚠️ File not found in R2 storage");
                    }
                } else {
                    $this->warn("  ⚠️ Unknown file path format: {$faxJob->file_path}");
                    $errorCount++;
                }

                // Clear the file_path in database if file was successfully deleted
                if ($fileDeleted) {
                    $faxJob->update(['file_path' => '']);
                    $this->line("  📝 Cleared file path from database");
                }

                Log::info("Fax document cleanup processed", [
                    'fax_job_id' => $faxJob->id,
                    'file_path' => $faxJob->file_path,
                    'file_deleted' => $fileDeleted,
                    'hours_old' => now()->diffInHours($faxJob->created_at),
                    'status' => $faxJob->status,
                    'scheduled_time' => $faxJob->scheduled_time?->toISOString(),
                    'is_scheduled' => !is_null($faxJob->scheduled_time),
                ]);

                // Small delay to avoid overwhelming storage APIs
                usleep(100000); // 0.1 seconds

            } catch (\Exception $e) {
                $errorCount++;
                $this->error("  ❌ Error processing fax job #{$faxJob->id}: " . $e->getMessage());

                Log::error("Failed to cleanup fax document", [
                    'fax_job_id' => $faxJob->id,
                    'file_path' => $faxJob->file_path,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Cleanup completed:");
        $this->info("  🗂️ Local files deleted: {$deletedLocal}");
        $this->info("  ☁️ R2 files deleted: {$deletedR2}");
        $this->info("  💾 Total space freed: {$this->formatBytes($totalSizeFreed)}");
        if ($errorCount > 0) {
            $this->warn("  ❌ Errors encountered: {$errorCount}");
        }

        Log::info("Fax document cleanup batch completed", [
            'total_jobs_processed' => $oldFaxJobs->count(),
            'local_files_deleted' => $deletedLocal,
            'r2_files_deleted' => $deletedR2,
            'total_space_freed' => $totalSizeFreed,
            'error_count' => $errorCount,
            'hours_threshold' => $hours,
            'protected_scheduled_faxes' => $protectedScheduledFaxes
        ]);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes): string
    {
        if ($bytes === 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
