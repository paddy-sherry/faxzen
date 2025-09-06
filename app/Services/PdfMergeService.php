<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfMergeService
{
    /**
     * Merge cover page with original document
     */
    public function mergePdfs(string $coverPagePath, string $originalDocumentPath): ?string
    {
        try {
            // Initialize FPDI
            $pdf = new Fpdi();
            
            // Add cover page
            $this->addPdfPages($pdf, $coverPagePath);
            
            // Add original document (only if it's a PDF)
            if ($this->isPdf($originalDocumentPath)) {
                $this->addPdfPages($pdf, $originalDocumentPath);
            } else {
                // For non-PDF files, we'll just use the cover page
                // The original document will be converted by Telnyx
                Log::info('Original document is not PDF, using cover page only', [
                    'original_path' => $originalDocumentPath
                ]);
            }
            
            // Generate merged PDF content
            $mergedContent = $pdf->Output('', 'S');
            
            // Save merged PDF
            $mergedFilename = 'merged_' . time() . '.pdf';
            $mergedPath = 'temp_fax_documents/' . $mergedFilename;
            
            Storage::disk('local')->put($mergedPath, $mergedContent);
            
            Log::info('PDFs merged successfully', [
                'cover_page_path' => $coverPagePath,
                'original_path' => $originalDocumentPath,
                'merged_path' => $mergedPath
            ]);
            
            return $mergedPath;
            
        } catch (\Exception $e) {
            Log::error('Failed to merge PDFs', [
                'cover_page_path' => $coverPagePath,
                'original_path' => $originalDocumentPath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Add all pages from a PDF file to the main PDF
     */
    protected function addPdfPages(Fpdi $pdf, string $pdfPath): void
    {
        $fullPath = Storage::disk('local')->path($pdfPath);
        $pageCount = $pdf->setSourceFile($fullPath);
        
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pdf->AddPage();
            $templateId = $pdf->importPage($pageNo);
            $pdf->useTemplate($templateId);
        }
    }

    /**
     * Check if file is a PDF
     */
    protected function isPdf(string $filePath): bool
    {
        if (!Storage::disk('local')->exists($filePath)) {
            return false;
        }
        
        $fullPath = Storage::disk('local')->path($filePath);
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fullPath);
        
        return $mimeType === 'application/pdf';
    }

    /**
     * Clean up temporary merged file
     */
    public function cleanupMergedFile(string $mergedPath): void
    {
        try {
            if (Storage::disk('local')->exists($mergedPath)) {
                Storage::disk('local')->delete($mergedPath);
                Log::info('Merged file cleaned up', ['path' => $mergedPath]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup merged file', [
                'path' => $mergedPath,
                'error' => $e->getMessage()
            ]);
        }
    }
}
