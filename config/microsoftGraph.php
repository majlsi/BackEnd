<?php

    return [
        'apiBaseURL' => env('MICROSOFT_API_BASE_URL','https://graph.microsoft.com/beta/'),
        'loginBaseURL' => env('MICROSOFT_LOGIN_BASE_URL','https://login.microsoftonline.com/'),
        'loginEndPoint' => env('MICROSOFT_LOGIN_END_POINT','/oauth2/v2.0/token'),
        'scope' => env('MICROSOFT_SCOPE','https://graph.microsoft.com/.default'),
        'grantType' => env('MICROSOFT_GRANT_TYPE','client_credentials'),
        'successCode' => env('MICROSOFT_SUCCESS_CODE',200),
        'meetingSuccessCode' =>  env('MICROSOFT_MEETING_SUCCESS_CODE',201),
        'meetingType' => env('MICROSOFT_MEETING_TYPE','scheduled'),
    ];