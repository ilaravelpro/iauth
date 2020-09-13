<?php

return [
    'routes' => [
        'api' => [
            'status' => true
        ]
    ],
    'database' => [
        'migrations' => [
            'include' => true
        ],
    ],
    'modes' => [
        'authorize' => [
            'status' => true,
            'type' => 'auto',
            'password' => true
        ],
        'verify' => [
            'method' => 'all',
            'status' => true,
            'type' => 'auto',
            'ever' => true
        ],
        'register' => true,
        'recovery' => true,
        'revoke' => true,
        'get' => true,
        'update' => true,
    ],
];
?>
