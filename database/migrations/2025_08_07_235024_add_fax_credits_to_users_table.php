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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('fax_credits')->default(0)->after('email_verified_at');
            $table->timestamp('credits_purchased_at')->nullable()->after('fax_credits');
            $table->string('stripe_customer_id')->nullable()->after('credits_purchased_at');
            $table->boolean('password_set')->default(false)->after('stripe_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fax_credits', 'credits_purchased_at', 'stripe_customer_id', 'password_set']);
        });
    }
};
