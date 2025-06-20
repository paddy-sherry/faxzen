# Kraken.io File Compression Setup

This document explains how to set up Kraken.io API for file compression in the FaxZen application.

## Overview

FaxZen uses Kraken.io's API to compress files before sending them via fax. This helps ensure files are within size limits while **preserving quality through lossless compression**.

## Supported File Types

- **PDF**: Documents, forms, contracts
- **JPG/JPEG**: Photos, scanned documents  
- **PNG**: Screenshots, graphics with transparency
- **GIF**: Animated images, simple graphics
- **SVG**: Vector graphics, logos
- **WebP**: Modern web images

## Compression Strategy

The service uses **lossless compression** for all file types to ensure:
- **No quality degradation** - your documents look exactly the same
- **Smaller file sizes** - faster transmission and better compatibility
- **Preserved text clarity** - important for faxed documents
- **Maintained image details** - photos and graphics stay crisp

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

The service uses **lossless compression** for all file types to preserve quality:

- **PDF**: Lossless compression (no quality loss)
- **JPG/JPEG**: Lossless optimization (removes metadata, optimizes structure)
- **PNG**: Lossless compression (excellent size reduction without quality loss)
- **GIF**: Lossless (preserves animation and colors)
- **WebP**: Lossless mode (maintains perfect quality)
- **SVG**: Lossless optimization (removes unnecessary code)

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