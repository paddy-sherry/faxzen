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
            // Detailed status tracking
            $table->boolean('is_preparing')->default(true)->after('status');
            $table->boolean('is_sending')->default(false)->after('is_preparing');
            $table->boolean('is_delivered')->default(false)->after('is_sending');
            $table->boolean('email_sent')->default(false)->after('is_delivered');
            
            // Timestamps for each step
            $table->timestamp('prepared_at')->nullable()->after('email_sent');
            $table->timestamp('sending_started_at')->nullable()->after('prepared_at');
            $table->timestamp('delivered_at')->nullable()->after('sending_started_at');
            $table->timestamp('email_sent_at')->nullable()->after('delivered_at');
            
            // Additional tracking fields
            $table->text('delivery_details')->nullable()->after('email_sent_at');
            $table->string('telnyx_status')->nullable()->after('delivery_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'is_preparing',
                'is_sending', 
                'is_delivered',
                'email_sent',
                'prepared_at',
                'sending_started_at',
                'delivered_at',
                'email_sent_at',
                'delivery_details',
                'telnyx_status'
            ]);
        });
    }
};
