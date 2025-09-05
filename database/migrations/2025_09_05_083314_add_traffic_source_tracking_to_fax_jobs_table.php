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
            // Traffic source tracking fields
            $table->string('traffic_source')->nullable()->after('telnyx_status')->comment('adwords, organic, direct, referral, etc.');
            $table->string('utm_source')->nullable()->after('traffic_source')->comment('UTM source parameter');
            $table->string('utm_medium')->nullable()->after('utm_source')->comment('UTM medium parameter'); 
            $table->string('utm_campaign')->nullable()->after('utm_medium')->comment('UTM campaign parameter');
            $table->string('utm_term')->nullable()->after('utm_campaign')->comment('UTM term parameter (keywords)');
            $table->string('utm_content')->nullable()->after('utm_term')->comment('UTM content parameter');
            $table->string('referrer_url')->nullable()->after('utm_content')->comment('HTTP referrer URL');
            $table->json('tracking_data')->nullable()->after('referrer_url')->comment('Additional tracking metadata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'traffic_source',
                'utm_source',
                'utm_medium', 
                'utm_campaign',
                'utm_term',
                'utm_content',
                'referrer_url',
                'tracking_data'
            ]);
        });
    }
};