<?php

use App\Http\Controllers\FaxController;
use Illuminate\Support\Facades\Route;

// Homepage - Step 1: Upload PDF and enter fax number
Route::get('/', [FaxController::class, 'step1'])->name('fax.step1');
Route::post('/', [FaxController::class, 'processStep1']);

// Fax sending process routes
Route::prefix('fax')->name('fax.')->group(function () {
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
