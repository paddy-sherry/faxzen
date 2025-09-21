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
            // Email click tracking
            $table->timestamp('early_reminder_clicked_at')->nullable()->after('early_reminder_sent_at');
            $table->timestamp('reminder_clicked_at')->nullable()->after('reminder_email_sent_at');
            $table->integer('email_click_count')->default(0)->after('reminder_clicked_at');
            
            // Track which email type was clicked (early, reminder, etc.)
            $table->json('email_clicks')->nullable()->after('email_click_count');
            
            // UTM tracking for analytics
            $table->string('last_utm_source')->nullable()->after('email_clicks');
            $table->string('last_utm_medium')->nullable()->after('last_utm_source');
            $table->string('last_utm_campaign')->nullable()->after('last_utm_medium');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'early_reminder_clicked_at',
                'reminder_clicked_at', 
                'email_click_count',
                'email_clicks',
                'last_utm_source',
                'last_utm_medium',
                'last_utm_campaign'
            ]);
        });
    }
};