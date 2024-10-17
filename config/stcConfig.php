<?php

    return [
        'callBackUrl' => env('STC_CALLBACK_URL','https://cloud.stc.com.sa/v1/events/'),
        'tokenUrl' => env('STC_TOKEN_URL','https://connect.bluvalt.com/auth/realms/cartwheel/protocol/openid-connect/token'),
        'grantType' => env('MICROSOFT_GRANT_TYPE','client_credentials'),
        'successCode' => env('STC_SUCCESS_CODE',200),
        'clientId' => env('STC_CLIENT_ID','f06f3aa5-3dca-4c59-b488-ca2aa5e0d99b'),
        'clientSecret' => env('STC_CLIENT_SECRET','65d073ed-fc24-4c90-b754-2ce2676261df'),
        'ref' => env('STC_REF','mjlsi_test_'),
        'webhook_secret' => env('STC_WEBHOOK_SECRET','d96b7a0db3c6c7ae79abc9ea64a9ec9552018cdf8e7449769c7f9339a14e3664'),
        'password_encryption_key' => env('STC_PASSWORD_KEY','fSHf0eu84GcKBHJZNqN3CUk17kQwZLOKyLi_UOA8hgQ=')
    ];