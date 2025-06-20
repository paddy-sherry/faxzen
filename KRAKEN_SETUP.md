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

1. **Fast Upload**: User uploads file to local storage (instant - no network delay)
2. **Immediate Response**: User can proceed to Step 2 instantly
3. **Background Processing**: SendFaxJob moves file to R2 cloud storage
4. **Compression**: File is automatically compressed using Kraken.io API (lossless)
5. **Fax Sending**: Compressed version is used for fax transmission via Telnyx
6. **Cleanup**: Local and cloud files are deleted after 24 hours

## Performance Benefits

- **Instant Upload**: Files stored locally first - no cloud upload delay
- **Zero Wait Time**: Users proceed immediately to next step
- **Background Processing**: All heavy operations (R2 upload, compression) happen asynchronously
- **Better User Experience**: No browser timeouts or long waits
- **Reliable Processing**: Multiple fallback mechanisms ensure delivery
- **Scalable**: Queue workers handle all heavy lifting

## Upload Flow Optimization

### Traditional Flow (Slow):
```
Upload → R2 → Compression → Response (5-30 seconds)
```

### Optimized Flow (Fast):
```
Upload → Local Storage → Response (< 1 second)
         ↓ (Background)
         R2 → Compression → Fax Sending
```

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