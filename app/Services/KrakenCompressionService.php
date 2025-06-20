<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class KrakenCompressionService
{
    private $apiKey;
    private $apiSecret;
    private $apiUrl = 'https://api.kraken.io/v1/upload';

    public function __construct()
    {
        $this->apiKey = config('services.kraken.api_key');
        $this->apiSecret = config('services.kraken.api_secret');
    }

    /**
     * Compress a file and store it back to the disk
     */
    public function compressAndStore($filePath, $disk = 'r2')
    {
        try {
            // Check if Kraken.io is configured
            if (!$this->apiKey || !$this->apiSecret) {
                Log::warning('Kraken.io API credentials not configured');
                return $this->getFallbackResult($filePath, $disk);
            }

            // Get the file from storage
            $fileContent = Storage::disk($disk)->get($filePath);
            $originalSize = Storage::disk($disk)->size($filePath);
            
            // Get file extension and MIME type
            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeType = $this->getMimeType($fileExtension);
            
            if (!$mimeType) {
                Log::warning("Unsupported file type: {$fileExtension}");
                return $this->getFallbackResult($filePath, $disk);
            }

            // Prepare compression settings based on file type
            $compressionSettings = $this->getCompressionSettings($fileExtension, $originalSize);

            // Prepare the JSON data as required by Kraken.io API
            $jsonData = [
                'auth' => [
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret
                ],
                'wait' => true,
                'lossy' => $compressionSettings['lossy'],
                'quality' => $compressionSettings['quality'],
            ];

            // Add resize if specified
            if (!empty($compressionSettings['resize'])) {
                $jsonData['resize'] = $compressionSettings['resize'];
            }

            // Prepare the request using multipart form data
            $response = Http::attach(
                'upload', $fileContent, basename($filePath)
            )->post($this->apiUrl, [
                'data' => json_encode($jsonData)
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                Log::info("Kraken.io API response received", [
                    'status' => $response->status(),
                    'result' => $result
                ]);
                
                if ($result['success']) {
                    // Download the compressed file
                    $compressedContent = Http::get($result['kraked_url'])->body();
                    
                    // Generate new filename for compressed version
                    $compressedPath = $this->generateCompressedPath($filePath);
                    
                    // Store the compressed file
                    Storage::disk($disk)->put($compressedPath, $compressedContent);
                    
                    $compressedSize = Storage::disk($disk)->size($compressedPath);
                    $compressionRatio = $originalSize > 0 ? (($originalSize - $compressedSize) / $originalSize) * 100 : 0;
                    
                    Log::info("File compressed successfully", [
                        'original_path' => $filePath,
                        'compressed_path' => $compressedPath,
                        'original_size' => $originalSize,
                        'compressed_size' => $compressedSize,
                        'compression_ratio' => $compressionRatio
                    ]);
                    
                    return [
                        'success' => true,
                        'compressed_path' => $compressedPath,
                        'original_size' => $originalSize,
                        'compressed_size' => $compressedSize,
                        'compression_ratio' => round($compressionRatio, 2),
                        'is_compressed' => true,
                        'savings_bytes' => $result['saved_bytes'] ?? ($originalSize - $compressedSize),
                    ];
                } else {
                    Log::error('Kraken.io compression failed', ['error' => $result['message'] ?? 'Unknown error']);
                    return $this->getFallbackResult($filePath, $disk);
                }
            } else {
                Log::error('Kraken.io API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'request_url' => $this->apiUrl,
                    'request_data' => $jsonData
                ]);
                return $this->getFallbackResult($filePath, $disk);
            }
        } catch (\Exception $e) {
            Log::error('Kraken.io compression exception', [
                'error' => $e->getMessage(),
                'file' => $filePath
            ]);
            return $this->getFallbackResult($filePath, $disk);
        }
    }

    /**
     * Get compression settings based on file type and size
     */
    private function getCompressionSettings($fileExtension, $fileSize)
    {
        $settings = [
            'lossy' => true,
            'quality' => 85,
        ];

        switch ($fileExtension) {
            case 'pdf':
                $settings['quality'] = 85;
                // For large PDFs, be more aggressive
                if ($fileSize > 10 * 1024 * 1024) { // 10MB
                    $settings['quality'] = 75;
                }
                break;
                
            case 'jpg':
            case 'jpeg':
                $settings['quality'] = 85;
                if ($fileSize > 5 * 1024 * 1024) { // 5MB
                    $settings['quality'] = 75;
                }
                break;
                
            case 'png':
                $settings['lossy'] = true;
                $settings['quality'] = 85;
                break;
                
            case 'gif':
                $settings['lossy'] = false; // GIFs should preserve animation
                break;
                
            case 'webp':
                $settings['quality'] = 85;
                break;
                
            case 'svg':
                $settings['lossy'] = false; // SVGs are vector-based
                break;
        }

        return $settings;
    }

    /**
     * Get MIME type for file extension
     */
    private function getMimeType($extension)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];

        return $mimeTypes[$extension] ?? null;
    }

    /**
     * Generate compressed file path
     */
    private function generateCompressedPath($originalPath)
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_compressed.' . $pathInfo['extension'];
    }

    /**
     * Get fallback result when compression fails
     */
    private function getFallbackResult($filePath, $disk)
    {
        $originalSize = Storage::disk($disk)->size($filePath);
        
        return [
            'success' => false,
            'compressed_path' => $filePath, // Use original path
            'original_size' => $originalSize,
            'compressed_size' => $originalSize,
            'compression_ratio' => 0,
            'is_compressed' => false,
            'savings_bytes' => 0,
        ];
    }
} 