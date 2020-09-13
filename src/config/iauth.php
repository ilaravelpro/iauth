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
        'login' => [
            'status' => true,
            'type' => 'auto',
            'password' => true
        ],
        'register' => [
            'status' => true,
            'type' => 'auto'
        ],
        'forgot' => true,
        'logout' => true,
        'get' => true,
        'update' => true,
    ],
];
?>
