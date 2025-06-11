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
        Schema::create('fax_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_number');
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('file_path');
            $table->string('file_original_name');
            $table->decimal('amount', 8, 2)->default(5.00);
            $table->string('payment_intent_id')->nullable();
            $table->enum('status', ['pending', 'payment_pending', 'paid', 'sent', 'failed'])->default('pending');
            $table->string('telnyx_fax_id')->nullable();
            $table->integer('retry_attempts')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fax_jobs');
    }
};
