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

    /*
    |--------------------------------------------------------------------------
    | SMS
    |--------------------------------------------------------------------------
    |
    | Driver used by App\Services\Sms\SmsService to notify parents when their
    | child has been recognized and marked present. Supported drivers:
    |   log    - writes the message to storage/logs/laravel.log
    |   twilio - sends a real SMS through the Twilio REST API
    |   http   - posts JSON {to, from, message} to SMS_HTTP_URL (custom gateway)
    |
    | The face recognition matching threshold is also configured here.
    | face-api.js descriptors are compared with Euclidean distance; 0.6 is the
    | commonly recommended maximum distance for a positive match.
    |
    */

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'from' => env('SMS_FROM', 'FRBAAFS'),

        'twilio' => [
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM'),
        ],

        'http' => [
            'url' => env('SMS_HTTP_URL'),
        ],
    ],

    'face_recognition' => [
        // Maximum Euclidean distance between descriptors for a match.
        'threshold' => (float) env('FACE_MATCH_THRESHOLD', 0.6),
    ],

    'attendance' => [
        // Time (H:i, app timezone) after which a student is marked "late".
        'school_starts' => env('SCHOOL_STARTS', '08:00'),
    ],

];
