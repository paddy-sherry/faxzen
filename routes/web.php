<?php

use App\Http\Controllers\FaxController;
use App\Http\Controllers\TelnyxWebhookController;
use Illuminate\Support\Facades\Route;

// Homepage - Step 1: Upload PDF and enter fax number
Route::match(['GET', 'POST'], '/', [FaxController::class, 'step1'])->name('fax.step1');

// Fax sending process routes
Route::prefix('fax')->name('fax.')->group(function () {
    // Step 2: Enter sender details
    Route::get('/step2/{faxJob:hash}', [FaxController::class, 'step2'])->name('step2');
    Route::post('/step2/{faxJob:hash}', [FaxController::class, 'processStep2'])->name('step2.process');
    
    // Payment success - redirect to status
    Route::get('/payment/success/{faxJob:hash}', [FaxController::class, 'paymentSuccess'])->name('payment.success');
    
    // Status tracking page
    Route::get('/status/{faxJob:hash}', [FaxController::class, 'status'])->name('status');
    
    // Document serving route (for Telnyx to access PDFs via temporary signed URLs)
    Route::get('/document/{filename}', function ($filename) {
        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        $path = storage_path('app/fax_documents/' . $filename);
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
        ]);
    })->name('document');
});

// Webhook endpoint removed - using console job polling instead

// Static pages
Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// Admin routes with HTTP Basic Auth
Route::middleware(['auth.basic'])->group(function () {
    Route::get('/admin/fax-jobs', [App\Http\Controllers\AdminController::class, 'faxJobs'])->name('admin.fax-jobs');
});


