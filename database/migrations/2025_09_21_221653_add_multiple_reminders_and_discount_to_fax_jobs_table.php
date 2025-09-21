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
            // Multiple reminder stages
            $table->boolean('early_reminder_sent')->default(false)->after('reminder_email_sent');
            $table->timestamp('early_reminder_sent_at')->nullable()->after('early_reminder_sent');
            
            // Discount system
            $table->string('discount_code')->nullable()->after('early_reminder_sent_at');
            $table->decimal('discount_amount', 8, 2)->default(0)->after('discount_code');
            $table->decimal('original_amount', 8, 2)->nullable()->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'early_reminder_sent',
                'early_reminder_sent_at',
                'discount_code',
                'discount_amount',
                'original_amount'
            ]);
        });
    }
};