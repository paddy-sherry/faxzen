<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Stripe Configuration
    'stripe' => [
        'model' => env('STRIPE_MODEL', App\Models\User::class),
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    // Telnyx Configuration
    'telnyx' => [
        'api_key' => env('TELNYX_API_KEY'),
        'api_base' => env('TELNYX_API_BASE', 'https://api.telnyx.com'),
        'connection_id' => env('TELNYX_CONNECTION_ID'),
        'from_number' => env('TELNYX_FROM_NUMBER'),
    ],

    // Kraken.io Configuration
    'kraken' => [
        'api_key' => env('KRAKEN_API_KEY'),
        'api_secret' => env('KRAKEN_API_SECRET'),
    ],

    // FaxZen Configuration
    'faxzen' => [
        'price' => env('FAXZEN_PRICE', 3.00),
    ],

];
