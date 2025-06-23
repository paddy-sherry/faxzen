<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            // Add hash column for secure URLs
            $table->string('hash', 32)->unique()->nullable()->after('id');
            
            // Add file size tracking
            $table->bigInteger('original_file_size')->nullable()->after('file_original_name');
        });

        // Populate hash field for existing records
        DB::table('fax_jobs')->whereNull('hash')->update([
            'hash' => DB::raw('SUBSTRING(MD5(RAND()), 1, 32)')
        ]);

        // Make hash non-nullable after populating
        Schema::table('fax_jobs', function (Blueprint $table) {
            $table->string('hash', 32)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            $table->dropColumn(['hash', 'original_file_size']);
        });
    }
};
