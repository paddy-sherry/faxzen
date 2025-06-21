<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# FaxZen - Online Fax Service

## Webhook Setup

### Telnyx Webhook Configuration

To enable real-time status updates, you need to configure webhooks in TWO places:

#### 1. Add webhook URL to your .env file:
```env
TELNYX_WEBHOOK_URL=https://yourdomain.com/webhooks/telnyx
```

#### 2. Configure webhooks in your Telnyx dashboard:
1. Go to [Telnyx Dashboard > Webhooks](https://portal.telnyx.com/#/app/webhooks)
2. Click "Add Webhook"
3. Configure:
   - **Webhook URL**: `https://yourdomain.com/webhooks/telnyx`
   - **HTTP Method**: POST
   - **Events**: Select all fax events:
     - `fax.sending`
     - `fax.delivered` 
     - `fax.failed`
     - `fax.media.processed`

**Important**: The webhook URL in your .env file tells Telnyx where to send status updates for each individual fax. The dashboard webhook is a fallback for all events.

### Backup Status Checking

As a backup to webhooks, you can run the status checker command:

```bash
# Check all undelivered faxes from last 2 hours
php artisan fax:check-status

# Check specific fax job
php artisan fax:check-status --job-id=58

# Check faxes from last 6 hours
php artisan fax:check-status --hours=6
```

### Automatic Status Checking

The system is configured to automatically check fax status every 2 minutes using Laravel's task scheduler.

#### Development
Start the scheduler:
```bash
php artisan schedule:work
```

#### Production
Add this to your crontab to run Laravel's scheduler:
```bash
* * * * * cd /path/to/faxzen && php artisan schedule:run >> /dev/null 2>&1
```

#### Manual Override
You can still run manual checks:
```bash
*/5 * * * * cd /path/to/faxzen && php artisan fax:check-status >/dev/null 2>&1
```

## Status Tracking

The system now tracks 4 distinct steps:

1. **Preparing Fax** - File uploaded, payment confirmed, queued for processing
2. **Sending** - File compressed and submitted to Telnyx API
3. **Delivered** - Confirmed delivery by receiving fax machine (via webhook/API)
4. **Email Confirmation** - Receipt email sent to customer

Each step has its own timestamp and the status page updates in real-time.
