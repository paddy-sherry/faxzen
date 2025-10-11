<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfMergeService
{
    private $convertedFiles = [];
    /**
     * Merge cover page with original document(s)
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
     * Merge cover page with multiple documents
     */
    public function mergeMultiplePdfs(?string $coverPagePath, array $documentPaths): ?string
    {
        try {
            Log::info('Starting mergeMultiplePdfs', [
                'cover_page_path' => $coverPagePath,
                'document_paths' => $documentPaths,
                'document_count' => count($documentPaths)
            ]);
            
            // Initialize FPDI
            $pdf = new Fpdi();
            
            // Add cover page if provided
            if ($coverPagePath) {
                try {
                    Log::info('Adding cover page to merge', ['cover_page_path' => $coverPagePath]);
                    $this->addPdfPages($pdf, $coverPagePath);
                } catch (\Exception $e) {
                    Log::warning('Failed to add cover page to merge', [
                        'cover_page_path' => $coverPagePath,
                        'error' => $e->getMessage(),
                        'will_continue_without_cover' => true
                    ]);
                    // Continue without cover page
                }
            }
            
            // Add all documents
            foreach ($documentPaths as $index => $documentPath) {
                Log::info('Processing document for merge', [
                    'index' => $index,
                    'document_path' => $documentPath,
                    'is_pdf' => $this->isPdf($documentPath)
                ]);
                
                if ($this->isPdf($documentPath)) {
                    try {
                        $this->addPdfPagesFromFile($pdf, $documentPath);
                        Log::info('Added PDF document to merge', ['document_path' => $documentPath]);
                    } catch (\Exception $e) {
                        Log::warning('Failed to add PDF document to merge, attempting conversion', [
                            'document_path' => $documentPath,
                            'error' => $e->getMessage()
                        ]);
                        
                        // Try to convert the PDF to a compatible format
                        $convertedPath = $this->convertPdfForMerging($documentPath);
                        if ($convertedPath) {
                            try {
                                $this->addPdfPagesFromFile($pdf, $convertedPath);
                                Log::info('Successfully added converted PDF document to merge', [
                                    'original_path' => $documentPath,
                                    'converted_path' => $convertedPath
                                ]);
                            } catch (\Exception $e2) {
                                Log::error('Failed to add converted PDF document to merge', [
                                    'original_path' => $documentPath,
                                    'converted_path' => $convertedPath,
                                    'error' => $e2->getMessage()
                                ]);
                                throw new \Exception("Unable to merge PDF document: {$documentPath}. Original error: {$e->getMessage()}. Conversion error: {$e2->getMessage()}");
                            }
                        } else {
                            throw new \Exception("Unable to merge PDF document: {$documentPath}. Error: {$e->getMessage()}");
                        }
                    }
                } else {
                    // For non-PDF files, we'll just skip them
                    // They will be converted by Telnyx
                    Log::info('Document is not PDF, skipping from merge', [
                        'document_path' => $documentPath
                    ]);
                }
            }
            
            // Generate merged PDF content
            $mergedContent = $pdf->Output('', 'S');
            
            // Save merged PDF
            $mergedFilename = 'merged_' . time() . '_' . uniqid() . '.pdf';
            $mergedPath = 'temp_fax_documents/' . $mergedFilename;
            
            Storage::disk('local')->put($mergedPath, $mergedContent);
            
            Log::info('Multiple PDFs merged successfully', [
                'cover_page_path' => $coverPagePath,
                'document_paths' => $documentPaths,
                'merged_path' => $mergedPath,
                'has_cover_page' => !is_null($coverPagePath)
            ]);
            
            return $mergedPath;
            
        } catch (\Exception $e) {
            Log::error('Failed to merge multiple PDFs', [
                'cover_page_path' => $coverPagePath,
                'document_paths' => $documentPaths,
                'has_cover_page' => !is_null($coverPagePath),
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
        
        Log::info('Adding PDF pages', [
            'pdf_path' => $pdfPath,
            'full_path' => $fullPath,
            'page_count' => $pageCount
        ]);
        
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pdf->AddPage();
            $templateId = $pdf->importPage($pageNo);
            $pdf->useTemplate($templateId);
        }
        
        Log::info('Finished adding PDF pages', [
            'pdf_path' => $pdfPath,
            'pages_added' => $pageCount
        ]);
    }

    /**
     * Add all pages from a PDF file to the main PDF (for multiple file merging)
     */
    protected function addPdfPagesFromFile(Fpdi $pdf, string $pdfPath): void
    {
        $fullPath = Storage::disk('local')->path($pdfPath);
        
        Log::info('Adding PDF pages from file', [
            'pdf_path' => $pdfPath,
            'full_path' => $fullPath
        ]);
        
        // Set the source file for this specific document
        $pageCount = $pdf->setSourceFile($fullPath);
        
        Log::info('PDF file loaded', [
            'pdf_path' => $pdfPath,
            'page_count' => $pageCount
        ]);
        
        // Add all pages from this document
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pdf->AddPage();
            $templateId = $pdf->importPage($pageNo);
            $pdf->useTemplate($templateId);
        }
        
        Log::info('Finished adding PDF pages from file', [
            'pdf_path' => $pdfPath,
            'pages_added' => $pageCount
        ]);
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

    /**
     * Clean up all converted files
     */
    public function cleanupConvertedFiles(): void
    {
        foreach ($this->convertedFiles as $convertedPath) {
            try {
                if (Storage::disk('local')->exists($convertedPath)) {
                    Storage::disk('local')->delete($convertedPath);
                    Log::info('Converted file cleaned up', ['path' => $convertedPath]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to cleanup converted file', [
                    'path' => $convertedPath,
                    'error' => $e->getMessage()
                ]);
            }
        }
        $this->convertedFiles = [];
    }

    /**
     * Convert a problematic PDF to a compatible format for merging using Ghostscript
     * This method uses Ghostscript to convert PDFs with compression issues
     */
    protected function convertPdfForMerging(string $originalPath): ?string
    {
        try {
            Log::info('Attempting to convert PDF for merging using Ghostscript', ['original_path' => $originalPath]);
            
            $fullPath = Storage::disk('local')->path($originalPath);
            $convertedFilename = 'converted_' . time() . '_' . uniqid() . '.pdf';
            $convertedPath = 'temp_fax_documents/' . $convertedFilename;
            $convertedFullPath = Storage::disk('local')->path($convertedPath);
            
            Log::info('Ghostscript file paths', [
                'original_path' => $originalPath,
                'full_path' => $fullPath,
                'converted_path' => $convertedPath,
                'converted_full_path' => $convertedFullPath,
                'original_exists' => file_exists($fullPath),
                'converted_dir' => dirname($convertedFullPath)
            ]);
            
            // Ensure the directory exists
            $convertedDir = dirname($convertedFullPath);
            if (!is_dir($convertedDir)) {
                mkdir($convertedDir, 0755, true);
            }
            
            // Use Ghostscript to convert the PDF to a compatible format
            $command = sprintf(
                'gs -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/prepress -sOutputFile=%s %s 2>&1',
                escapeshellarg($convertedFullPath),
                escapeshellarg($fullPath)
            );
            
            Log::info('Running Ghostscript command', ['command' => $command]);
            
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                Log::error('Ghostscript conversion failed', [
                    'original_path' => $originalPath,
                    'return_code' => $returnCode,
                    'output' => implode("\n", $output)
                ]);
                return null;
            }
            
            // Verify the converted file exists and is readable
            if (!Storage::disk('local')->exists($convertedPath)) {
                Log::error('Converted PDF file was not created', [
                    'original_path' => $originalPath,
                    'converted_path' => $convertedPath
                ]);
                return null;
            }
            
            // Test if the converted PDF can be read by FPDI
            try {
                $testPdf = new Fpdi();
                $testPdf->setSourceFile($convertedFullPath);
                Log::info('Converted PDF is compatible with FPDI', [
                    'original_path' => $originalPath,
                    'converted_path' => $convertedPath
                ]);
            } catch (\Exception $e) {
                Log::error('Converted PDF is still not compatible with FPDI', [
                    'original_path' => $originalPath,
                    'converted_path' => $convertedPath,
                    'error' => $e->getMessage()
                ]);
                // Clean up the failed conversion
                Storage::disk('local')->delete($convertedPath);
                return null;
            }
            
            Log::info('PDF converted successfully for merging', [
                'original_path' => $originalPath,
                'converted_path' => $convertedPath,
                'ghostscript_output' => implode("\n", $output)
            ]);
            
            // Track converted file for cleanup
            $this->convertedFiles[] = $convertedPath;
            
            return $convertedPath;
            
        } catch (\Exception $e) {
            Log::error('Failed to convert PDF for merging', [
                'original_path' => $originalPath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
