# Kraken.io Integration Setup

This document explains how to set up Kraken.io compression for FaxZen.

## Overview

FaxZen now supports automatic file compression using Kraken.io API for:
- PDF documents
- Images (JPG, PNG, GIF, WebP, SVG)

Files are automatically compressed before being sent via fax to improve delivery success rates and reduce transmission time.

## Setup Instructions

### 1. Create Kraken.io Account

1. Go to [https://kraken.io/](https://kraken.io/)
2. Sign up for an account
3. Choose a plan that fits your needs (they offer a free tier)

### 2. Get API Credentials

1. Log into your Kraken.io dashboard
2. Go to API Credentials section
3. Copy your API Key and API Secret

### 3. Configure Environment Variables

Add the following to your `.env` file:

```env
KRAKEN_API_KEY=your_api_key_here
KRAKEN_API_SECRET=your_api_secret_here
```

### 4. Test the Integration

The compression service will automatically:
- Compress files when they're uploaded
- Fall back to original files if compression fails
- Log compression results for monitoring

## How It Works

1. **File Upload**: User uploads PDF or image file
2. **Compression**: File is sent to Kraken.io for compression
3. **Storage**: Compressed file is stored alongside original
4. **Fax Sending**: Compressed version is used for fax transmission
5. **Cleanup**: Files are deleted after 24 hours

## Compression Settings

The service uses different settings based on file type:

- **PDF**: Quality 85% (75% for files > 10MB)
- **JPG/JPEG**: Quality 85% (75% for files > 5MB)  
- **PNG**: Lossy compression at 85% quality
- **GIF**: Lossless (preserves animation)
- **WebP**: Quality 85%
- **SVG**: Lossless (vector-based)

## Monitoring

Compression results are logged and stored in the database:
- `is_compressed`: Whether compression was successful
- `original_file_size`: Size before compression
- `compressed_file_size`: Size after compression  
- `compression_ratio`: Percentage reduction

## Fallback Behavior

If Kraken.io is unavailable or compression fails:
- Original file is used for fax transmission
- Error is logged for debugging
- Service continues to function normally

## Cost Considerations

Kraken.io charges based on:
- Number of API calls
- Total data processed

Monitor your usage in the Kraken.io dashboard to stay within your plan limits.

## Security

- API credentials are stored securely in environment variables
- Files are transmitted over HTTPS
- Compressed files are automatically deleted after 24 hours 