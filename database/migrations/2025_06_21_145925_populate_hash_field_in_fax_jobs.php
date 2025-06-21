<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add hash column if it doesn't exist
        if (!Schema::hasColumn('fax_jobs', 'hash')) {
            Schema::table('fax_jobs', function (Blueprint $table) {
                $table->string('hash', 64)->nullable()->after('id');
            });
        }

        // Generate hashes for existing records that don't have them
        \DB::table('fax_jobs')->whereNull('hash')->orWhere('hash', '')->orderBy('id')->chunk(100, function ($faxJobs) {
            foreach ($faxJobs as $faxJob) {
                \DB::table('fax_jobs')
                    ->where('id', $faxJob->id)
                    ->update(['hash' => Str::random(32)]);
            }
        });

        // Make the hash field unique and indexed (only if not already exists)
        try {
            Schema::table('fax_jobs', function (Blueprint $table) {
                $table->unique('hash');
            });
        } catch (\Exception $e) {
            // Unique constraint already exists
        }

        try {
            Schema::table('fax_jobs', function (Blueprint $table) {
                $table->index('hash');
            });
        } catch (\Exception $e) {
            // Index already exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            $table->dropUnique(['hash']);
            $table->dropIndex(['hash']);
        });
    }
};
