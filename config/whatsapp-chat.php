<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business Cloud API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WhatsApp Business Cloud API integration.
    | These values should be set in your .env file.
    |
    */

    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
    'webhook_url' => env('WHATSAPP_WEBHOOK_URL', env('APP_URL') . '/whatsapp/webhook'),
    'use_mock_mode' => env('WHATSAPP_USE_MOCK_MODE', true),
    'auto_mock_on_token_expiry' => env('WHATSAPP_AUTO_MOCK_ON_TOKEN_EXPIRY', true),

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    */

    'api_version' => 'v18.0',
    'base_url' => 'https://graph.facebook.com',

    /*
    |--------------------------------------------------------------------------
    | Message Settings
    |--------------------------------------------------------------------------
    */

    'max_message_length' => 4096,
    'message_retention_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    */

    'webhook_events' => [
        'messages',
        'message_deliveries',
        'message_reads',
        'message_reactions'
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */

    'route_prefix' => 'whatsapp',
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Broadcasting Configuration
    |--------------------------------------------------------------------------
    */

    'broadcasting' => [
        'enabled' => env('WHATSAPP_BROADCASTING_ENABLED', true),
        'driver' => env('WHATSAPP_BROADCASTING_DRIVER', 'pusher'),
        'channel_prefix' => 'whatsapp',
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    */

    'ui' => [
        'enabled' => env('WHATSAPP_UI_ENABLED', true),
        'theme' => env('WHATSAPP_UI_THEME', 'default'),
        'show_verification' => env('WHATSAPP_SHOW_VERIFICATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Messages
    |--------------------------------------------------------------------------
    */

    'templates' => [
        'welcome' => 'welcome_message',
        'confirmation' => 'deal_confirmation',
        'reminder' => 'payment_reminder'
    ]
];
