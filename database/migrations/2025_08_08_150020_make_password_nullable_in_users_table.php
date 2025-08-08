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
            // Make password nullable for passwordless authentication
            $table->string('password')->nullable()->change();
            
            // Drop password_set field since we're fully passwordless now
            $table->dropColumn('password_set');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add back password_set field
            $table->boolean('password_set')->default(false)->after('stripe_customer_id');
            
            // Make password required again (note: this would require setting passwords for existing users)
            $table->string('password')->nullable(false)->change();
        });
    }
};
