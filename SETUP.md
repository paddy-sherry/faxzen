# FaxZen Setup Guide

Welcome to FaxZen - your online fax sending service built with Laravel! This guide will help you set up the application for production use.

## Prerequisites

- PHP 8.2 or higher
- Composer
- SQLite (already configured)
- Stripe account for payments
- Telnyx account for fax services

## Environment Configuration

You'll need to set up the following environment variables in your `.env` file:

### Stripe Configuration (Required)
1. Sign up for a Stripe account at https://stripe.com
2. Get your API keys from the Stripe Dashboard
3. Add these to your `.env` file:

```env
STRIPE_KEY=pk_test_your_publishable_key_here
STRIPE_SECRET=sk_test_your_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
```

### Telnyx Configuration (Required)
1. Sign up for a Telnyx account at https://telnyx.com
2. Set up a Fax Application in your Telnyx Portal
3. Get your API credentials and connection ID
4. Add these to your `.env` file:

```env
TELNYX_API_KEY=your_telnyx_api_key_here
TELNYX_CONNECTION_ID=your_connection_id_here
TELNYX_FROM_NUMBER=+1234567890
TELNYX_WEBHOOK_URL=https://yourdomain.com/webhooks/telnyx
```

**Important**: The webhook URL enables real-time status updates. Without it, you'll need to rely on the backup status checker command.

### FaxZen Configuration (Optional)
Configure the fax service pricing:

```env
FAXZEN_PRICE=3.00
```

### Email Configuration (Optional but Recommended)
For sending confirmation emails, configure your mail settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email_username
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@faxzen.com
MAIL_FROM_NAME="FaxZen"
```

## Installation Steps

1. **Install Dependencies** (Already done)
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

2. **Database Setup** (Already done)
   ```bash
   php artisan migrate
   ```

3. **Storage Setup** (Already done)
   ```bash
   php artisan storage:link
   ```

4. **Queue Worker**
   For production, set up a queue worker to process fax jobs:
   ```bash
   php artisan queue:work --tries=3 --timeout=90 --daemon
   ```

   Or add this to your supervisor configuration:
   ```ini
   [program:faxzen-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /path/to/faxzen/artisan queue:work --sleep=3 --tries=3 --timeout=90
   autostart=true
   autorestart=true
   user=www-data
   numprocs=2
   redirect_stderr=true
   stdout_logfile=/path/to/faxzen/storage/logs/worker.log
   ```

## Features

### 3-Step Fax Process
1. **Step 1**: Upload PDF document and enter destination fax number
2. **Step 2**: Enter sender name and email address
3. **Step 3**: Complete payment via Stripe and send fax

### Key Features
- **$5 flat fee** for all faxes (configurable in the database)
- **Automatic retry logic**: Retries failed faxes after 30s, then 60s
- **Secure file handling**: Files stored locally and served via secure routes
- **Payment processing**: Secure Stripe integration
- **Modern UI**: Responsive design with Tailwind CSS

## Testing the Application

1. **Start the development server**:
   ```bash
   php artisan serve
   ```

2. **Start the queue worker** (in a separate terminal):
   ```bash
   php artisan queue:work
   ```

3. **Visit** `http://localhost:8000` to test the application

## Production Deployment

### Security Considerations
- Set `APP_ENV=production` and `APP_DEBUG=false`
- Use HTTPS for all connections
- Set up proper file permissions (755 for directories, 644 for files)
- Configure proper web server (Nginx/Apache) with PHP-FPM

### Performance Optimization
- Enable OPcache in PHP
- Use Redis for caching and sessions in production
- Set up Laravel's task scheduler for maintenance tasks

### File Storage
- Consider using Amazon S3 or similar for file storage in production
- Set up automatic cleanup of old fax documents

## Troubleshooting

### Common Issues

1. **File upload errors**: Check PHP `upload_max_filesize` and `post_max_size` settings
2. **Queue not processing**: Ensure the queue worker is running
3. **Stripe webhook issues**: Verify webhook endpoint and secret key
4. **Telnyx API errors**: Check API key and connection ID configuration

### Logs
- Application logs: `storage/logs/laravel.log`
- Queue worker logs: Use supervisor or similar process manager

## Support

For technical support or questions about the implementation, check the Laravel documentation and the Stripe/Telnyx API documentation.

## License

This application is built for faxzen.com migration from WordPress to Laravel. 