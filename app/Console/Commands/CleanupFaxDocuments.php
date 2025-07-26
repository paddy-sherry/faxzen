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
    protected $description = 'Clean up fax documents from local storage and R2 after specified hours (default: 48 hours)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        $dryRun = $this->option('dry-run');
        
        $this->info("Looking for fax documents older than {$hours} hours to clean up...");

        // Find fax jobs older than specified hours
        $oldFaxJobs = FaxJob::where('created_at', '<=', now()->subHours($hours))
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->get();

        if ($oldFaxJobs->isEmpty()) {
            $this->info('No old fax documents found to clean up.');
            return;
        }

        $this->info("Found {$oldFaxJobs->count()} old fax jobs with documents to clean up.");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will be deleted');
            $this->table(
                ['ID', 'File Path', 'Storage', 'Created', 'Hours Old', 'File Size'],
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
                    
                    return [
                        $job->id,
                        $job->file_path,
                        $storageType . ($fileExists ? ' ✓' : ' ✗'),
                        $job->created_at->format('M j, Y g:i A'),
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
                    'hours_old' => now()->diffInHours($faxJob->created_at)
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
            'hours_threshold' => $hours
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
