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
            'status' => true,
            'type' => 'auto',
            'ever' => true
        ],
        'forgot' => true,
        'logout' => true,
        'get' => true,
        'update' => true,
    ],
];
?>
