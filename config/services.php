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

    'telegram' => [
        'transaction_bot_token' => env('TRANSACTION_BOT_TOKEN'),
        'transaction_bot_username' => env('TRANSACTION_BOT_USERNAME', 'transaction_bot'),
        'delivery_bot_token' => env('DELIVERY_BOT_TOKEN'),
        'delivery_bot_username' => env('DELIVERY_BOT_USERNAME', 'delivery_bot'),
        'channel_id' => env('TELEGRAM_CHANNEL_ID'),
        'channel_username' => env('TELEGRAM_CHANNEL_USERNAME'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
        'admin_chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),
        'qris_image_path' => env('QRIS_IMAGE_PATH', 'qris/qris_static.png'),
    ],

];
