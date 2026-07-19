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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-5'),
    ],

    'report_webhook' => [
        'url' => env('REPORT_WEBHOOK_URL'),
    ],

    'streampay' => [
        'base_url' => env('STREAMPAY_BASE_URL', 'https://stream-app-service.streampay.sa/api/v2'),
        'api_key' => env('STREAMPAY_API_KEY'),
        'api_secret' => env('STREAMPAY_API_SECRET'),
        'webhook_secret' => env('STREAMPAY_WEBHOOK_SECRET'),
        'product_id' => env('STREAMPAY_PRODUCT_ID'),
        'currency' => env('STREAMPAY_CURRENCY', 'SAR'),
    ],

];
