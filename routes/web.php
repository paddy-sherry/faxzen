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

// Blog routes
Route::get('/blog', [App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');

// Sitemap route
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Admin routes (authentication removed for easier access)
Route::get('/admin/fax-jobs', [App\Http\Controllers\AdminController::class, 'faxJobs'])->name('admin.fax-jobs');
Route::match(['GET', 'POST'], '/admin/fax-jobs/{id}/retry', [App\Http\Controllers\AdminController::class, 'retryFaxJob'])->name('admin.fax-jobs.retry');
Route::get('/admin/fax-jobs/{id}/check-status', [App\Http\Controllers\AdminController::class, 'checkStatus'])->name('admin.fax-jobs.check-status');

// Blog management routes
Route::prefix('admin/blog')->name('admin.blog.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\BlogController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Admin\BlogController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Admin\BlogController::class, 'store'])->name('store');
    Route::get('/{post}', [App\Http\Controllers\Admin\BlogController::class, 'show'])->name('show');
    Route::get('/{post}/edit', [App\Http\Controllers\Admin\BlogController::class, 'edit'])->name('edit');
    Route::put('/{post}', [App\Http\Controllers\Admin\BlogController::class, 'update'])->name('update');
    Route::delete('/{post}', [App\Http\Controllers\Admin\BlogController::class, 'destroy'])->name('destroy');
    Route::post('/{post}/publish', [App\Http\Controllers\Admin\BlogController::class, 'publish'])->name('publish');
    Route::post('/{post}/unpublish', [App\Http\Controllers\Admin\BlogController::class, 'unpublish'])->name('unpublish');
    Route::post('/{post}/duplicate', [App\Http\Controllers\Admin\BlogController::class, 'duplicate'])->name('duplicate');
});


