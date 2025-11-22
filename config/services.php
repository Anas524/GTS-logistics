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

    'fedex' => [
        'env'            => env('FEDEX_ENV', 'sandbox'),
        'client_id'      => env('FEDEX_CLIENT_ID'),
        'client_secret'  => env('FEDEX_CLIENT_SECRET'),
        'account_number' => env('FEDEX_ACCOUNT_NUMBER'),
        'oauth_url'      => env('FEDEX_OAUTH_URL'),
        'track_url'      => env('FEDEX_TRACK_URL'),
    ],

];
