<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 9:21 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

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
    'methods' => [
        'auth' => [
            'status' => true,
            'register' => true,
            'password' => [
                'status' => true,
                'after' => false
            ]
        ],
        'register' => [
            'status' => true,
        ],
        'verify' => [
            'status' => true,
            'auto' => false,
            'mode' => 'smart',
            'ever' => false,
            'other' => 'email',
        ],
        'recovery' => [
            'status' => true,
        ],
        'revoke' => [
            'status' => true,
        ],
        'get' => [
            'status' => true,
        ],
        'update' => [
            'status' => true,
        ],
    ],
    'bridges' => [
        'models' => [
            'mobile' => [
                'status' => true,
                'sessions' => ['auth', 'register', 'recovery', 'password', 'mobile']
            ],
            'email' => [
                'status' => true,
                'sessions' => ['auth', 'register', 'recovery', 'password', 'email']
            ],
            'password' => [
                'status' => true,
                'sessions' => ['auth']
            ]
        ]
    ],
    'sessions' => [
        'expired' => [
            'time' => 10,
            'count' => 3
        ],
        'models' => [
            'auth' => [
                'model' => \iLaravel\iAuth\Vendor\AuthTheory\Auth::class
            ]
        ]
    ]
];
?>
