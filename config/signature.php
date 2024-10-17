<?php

return [

    'SIGNATURE_URL' => env('SIGNATURE_URL', 'http://localhost:5000/api/'),
    'SIGNATURE_USERNAME' => env('SIGNATURE_USERNAME', 'yasser.mohamed@enozom.com'),
    'SIGNATURE_PASSWORD' => env('SIGNATURE_PASSWORD', '123'),
    'filedTypes' =>[
        'signature'=>1,
        'signatureButton'=>2
    ]
];