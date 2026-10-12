<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'paystack' => [
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'base_url' => 'https://api.paystack.co',
        'apple_pay' => env('PAYSTACK_APPLE_PAY', false),
        'fallback_email_domain' => env('PAYSTACK_FALLBACK_EMAIL_DOMAIN', 'safarihub.app'),
    ],
];
