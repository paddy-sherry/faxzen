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
            $table->json('command_output')->nullable()->after('retry_logs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            $table->dropColumn('command_output');
        });
    }
};