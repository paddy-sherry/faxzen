<?php

use App\Http\Controllers\FaxController;
use App\Http\Controllers\TelnyxWebhookController;
use Illuminate\Support\Facades\Route;

// Homepage - Step 1: Upload PDF and enter fax number
Route::match(['GET', 'POST'], '/', [FaxController::class, 'step1'])->name('fax.step1');

// Fax sending process routes
Route::prefix('fax')->name('fax.')->group(function () {
    // Step 2: Enter sender details
    Route::get('/step2/{faxJob}', [FaxController::class, 'step2'])->name('step2');
    Route::post('/step2/{faxJob}', [FaxController::class, 'processStep2'])->name('step2.process');
    
    // Payment success - redirect to status
    Route::get('/payment/success/{faxJob}', [FaxController::class, 'paymentSuccess'])->name('payment.success');
    
    // Status tracking page
    Route::get('/status/{faxJob}', [FaxController::class, 'status'])->name('status');
    
    // Document serving route (for Telnyx to access PDFs)
    Route::get('/document/{filename}', function ($filename) {
        $path = storage_path('app/fax_documents/' . $filename);
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
        ]);
    })->name('document');
});

// Telnyx webhook endpoint
Route::post('/webhooks/telnyx', [TelnyxWebhookController::class, 'handle'])->name('webhooks.telnyx');


