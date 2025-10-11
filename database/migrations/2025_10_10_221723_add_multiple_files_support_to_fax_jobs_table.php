<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            // Add support for multiple files (only if they don't exist)
            if (!Schema::hasColumn('fax_jobs', 'file_paths')) {
                $table->json('file_paths')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('fax_jobs', 'file_original_names')) {
                $table->json('file_original_names')->nullable()->after('file_original_name');
            }
            if (!Schema::hasColumn('fax_jobs', 'original_file_sizes')) {
                $table->json('original_file_sizes')->nullable()->after('original_file_size');
            }
            if (!Schema::hasColumn('fax_jobs', 'file_count')) {
                $table->integer('file_count')->default(1)->after('file_original_names');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            $table->dropColumn(['file_paths', 'file_original_names', 'original_file_sizes', 'file_count']);
        });
    }
};
