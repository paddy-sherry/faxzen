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
            // Cover page fields
            $table->boolean('include_cover_page')->default(false)->after('failure_email_sent')->comment('Whether to include a cover page');
            $table->string('cover_sender_name')->nullable()->after('include_cover_page')->comment('Sender name for cover page');
            $table->string('cover_sender_company')->nullable()->after('cover_sender_name')->comment('Sender company for cover page');
            $table->string('cover_sender_phone')->nullable()->after('cover_sender_company')->comment('Sender phone for cover page');
            $table->string('cover_recipient_name')->nullable()->after('cover_sender_phone')->comment('Recipient name for cover page');
            $table->string('cover_recipient_company')->nullable()->after('cover_recipient_name')->comment('Recipient company for cover page');
            $table->string('cover_subject')->nullable()->after('cover_recipient_company')->comment('Cover page subject line');
            $table->text('cover_message')->nullable()->after('cover_subject')->comment('Cover page message/notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'include_cover_page',
                'cover_sender_name',
                'cover_sender_company', 
                'cover_sender_phone',
                'cover_recipient_name',
                'cover_recipient_company',
                'cover_subject',
                'cover_message'
            ]);
        });
    }
};