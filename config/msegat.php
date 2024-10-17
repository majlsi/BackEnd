<?php

return [
    
    'USER_NAME'=>env('SMS_USERNAME', 'Hawsabah'),
    'APIKEY'=>env('SMS_APIKEY', '1ebd3155e1e72cf461038f5c17dea956'),
    'MSGENCODING'=>env('SMS_MSG_ENCODING','UTF8'),

    
    'URL'=>env('SMS_SEND_MSG_URL','https://www.msegat.com/gw/sendsms.php'),
];