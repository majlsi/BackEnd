<?php

return [
    
    'duration_per_minute' => env('FAILED_LOGIN_DURATION_PER_MINUTE',60),
    'number_of_attempts' => env('FAILED_LOGIN_ATTEMPTS_NUMBER',3000),
];