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
            // Only add original_file_size since hash already exists
            if (!Schema::hasColumn('fax_jobs', 'original_file_size')) {
                $table->bigInteger('original_file_size')->nullable()->after('file_original_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('fax_jobs', 'original_file_size')) {
                $table->dropColumn('original_file_size');
            }
        });
    }
};
