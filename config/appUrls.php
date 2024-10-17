<?php

return [
    "api" =>[
        'cloudUrl'=>env('API_CLOUD_URL', 'http://www.mjlsi.com/app/BackEnd/public'),
        'onPremisesUrl'=>env('API_PREMISES_URL', 'http://www.mjlsi.com/app/BackEnd/public'),
    ],
    "front" =>[
        'cloudUrl'=>env('FRONT_CLOUD_URL', 'http://www.mjlsi.com/app'),
        'onPremisesUrl'=>env('FRONT_PREMISES_URL', 'http://www.mjlsi.com/app'),
    ],
    'redis'=>[
        'cloudUrl'=>env('REDIS_CLOUD_URL', 'https://mjlsi.com:6001'),
        'onPremisesUrl'=>env('REDIS_PREMISES_URL', 'https://mjlsi.com:6001'),
    ],
    'guest' => [
        'invite' => env('APP_URL_FRONTEND', 'http://localhost:4200') . env("GUEST_REDIRECT_LINK", "/meeting?token=")
    ]
];

