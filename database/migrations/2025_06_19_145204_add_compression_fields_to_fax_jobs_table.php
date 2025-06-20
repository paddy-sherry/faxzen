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
            $table->boolean('is_compressed')->default(false)->after('error_message');
            $table->bigInteger('original_file_size')->nullable()->after('is_compressed');
            $table->bigInteger('compressed_file_size')->nullable()->after('original_file_size');
            $table->decimal('compression_ratio', 5, 2)->nullable()->after('compressed_file_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fax_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'is_compressed',
                'original_file_size',
                'compressed_file_size',
                'compression_ratio'
            ]);
        });
    }
};
