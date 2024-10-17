<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN', "mjlsi.com"),
        'secret' => env('MAILGUN_APIKEY', "7a897f7177cc75a506ed2d7e56479333-262b213e-f6b638c2")
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    'facebook' => [
        'client_id' => '158165745123051',
        'client_secret' => '92da14b159b4bd0fac86c9357877f607',
        'redirect' => env('APP_URL') . '/api/v1/social-callback/' . config('providers.facebook'),
    ],
    'google' => [
        'client_id' => '1010114452542-s0l3usl47mj1s8sjhnoocputgtvd85qq.apps.googleusercontent.com',
        'client_secret' => 'IkHyW5nSCqF3IlBwD4Hg5ozG',
        'redirect' => env('APP_URL') . '/api/v1/social-callback/' . config('providers.google'),
    ]
];
