<?php

return [
    
    'USER_NAME'=>env('SMS_USERNAME_SWCC', 'SWCC'),
    'MSGENCODING'=>env('SMS_MSG_ENCODING','UTF8'),
    'URL'=>env('SMS_SEND_MSG_URL_SWCC','https://apiext.swcc.gov.sa/SMSGateway_an/api/SMSGateway/SendSMS'),
];