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
            $table->boolean('reminder_email_sent')->default(false)->after('email_sent');
            $table->timestamp('reminder_email_sent_at')->nullable()->after('reminder_email_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            $table->dropColumn(['reminder_email_sent', 'reminder_email_sent_at']);
        });
    }
};
