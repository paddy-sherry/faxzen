<?php

namespace App\Services;

use App\Models\FaxJob;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CoverPageService
{
    /**
     * Generate a cover page PDF for the given fax job
     */
    public function generateCoverPage(FaxJob $faxJob): ?string
    {
        if (!$faxJob->include_cover_page) {
            return null;
        }

        try {
            $html = $this->generateCoverPageHTML($faxJob);
            $pdf = $this->createPdfFromHTML($html);
            
            // Save the cover page PDF to temporary storage
            $filename = 'cover_page_' . $faxJob->id . '_' . time() . '.pdf';
            $path = 'temp_fax_documents/' . $filename;
            
            Storage::disk('local')->put($path, $pdf);
            
            Log::info('Cover page generated successfully', [
                'fax_job_id' => $faxJob->id,
                'cover_page_path' => $path
            ]);
            
            return $path;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate cover page', [
                'fax_job_id' => $faxJob->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Generate HTML content for the cover page
     */
    protected function generateCoverPageHTML(FaxJob $faxJob): string
    {
        $date = now()->format('F j, Y');
        $time = now()->format('g:i A T');
        
        return view('fax.cover-page', [
            'faxJob' => $faxJob,
            'date' => $date,
            'time' => $time,
            'totalPages' => $this->calculateTotalPages($faxJob)
        ])->render();
    }

    /**
     * Create PDF from HTML using DomPDF
     */
    protected function createPdfFromHTML(string $html): string
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->output();
    }

    /**
     * Calculate total pages (cover page + document pages)
     * For now, we'll estimate document pages based on file size
     */
    protected function calculateTotalPages(FaxJob $faxJob): int
    {
        $coverPages = 1; // Always 1 cover page
        
        // Estimate document pages based on file size
        // This is a rough estimation - actual page count would require PDF parsing
        $fileSizeKB = $faxJob->original_file_size / 1024;
        $estimatedDocPages = max(1, (int)ceil($fileSizeKB / 100)); // ~100KB per page average
        
        return $coverPages + $estimatedDocPages;
    }

    /**
     * Clean up temporary cover page file
     */
    public function cleanupCoverPage(string $coverPagePath): void
    {
        try {
            if (Storage::disk('local')->exists($coverPagePath)) {
                Storage::disk('local')->delete($coverPagePath);
                Log::info('Cover page cleaned up', ['path' => $coverPagePath]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup cover page', [
                'path' => $coverPagePath,
                'error' => $e->getMessage()
            ]);
        }
    }
}
