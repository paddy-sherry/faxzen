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
            $table->timestamp('scheduled_time')->nullable()->after('sender_email')->comment('When the fax should be sent (null = immediately)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            $table->dropColumn('scheduled_time');
        });
    }
};