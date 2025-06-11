<?php

use App\Http\Controllers\FaxController;
use Illuminate\Support\Facades\Route;

// Homepage - redirect to step 1
Route::get('/', function () {
    return redirect()->route('fax.step1');
});

// Fax sending process routes
Route::prefix('fax')->name('fax.')->group(function () {
    // Step 1: Upload PDF and enter fax number
    Route::get('/step1', [FaxController::class, 'step1'])->name('step1');
    Route::post('/step1', [FaxController::class, 'processStep1']);
    
    // Step 2: Enter sender details
    Route::get('/step2/{faxJob}', [FaxController::class, 'step2'])->name('step2');
    Route::post('/step2/{faxJob}', [FaxController::class, 'processStep2']);
    
    // Payment success
    Route::get('/payment/success/{faxJob}', [FaxController::class, 'paymentSuccess'])->name('payment.success');
    
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
