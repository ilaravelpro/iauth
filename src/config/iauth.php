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
                'status' => false,
                'after' => true
            ]
        ],
        'register' => [
            'status' => true,
        ],
        'verify' => [
            'status' => true,
            'auto' => true,
            'mode' => 'smart',
            'ever' => true,
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
        'expired' => [
            'time' => 3,
            'count' => 3
        ],
        'models' => [
            'mobile' => [
                'status' => true,
                'sessions' => ['auth', 'register', 'recovery', 'password', 'mobile', 'mobile_change']
            ],
            'email' => [
                'status' => true,
                'sessions' => ['auth', 'register', 'recovery', 'password', 'email', 'email_change']
            ],
            'password' => [
                'status' => true,
                'sessions' => ['auth']
            ]
        ]
    ],
    'sessions' => [
        'expired' => [
            'time' => 60,
            'count' => 3
        ],
        'models' => [
            'auth' => [
                'model' => \iLaravel\iAuth\Vendor\AuthSession\Auth::class
            ],
            'recovery' => [
                'model' => \iLaravel\iAuth\Vendor\AuthSession\Recovery::class
            ],
            'email' => [
                'model' => \iLaravel\iAuth\Vendor\AuthSession\Email::class
            ],
            'email_change' => [
                'model' => \iLaravel\iAuth\Vendor\AuthSession\EmailChange::class
            ],
            'mobile' => [
                'model' => \iLaravel\iAuth\Vendor\AuthSession\Mobile::class
            ],
            'mobile_change' => [
                'model' => \iLaravel\iAuth\Vendor\AuthSession\MobileChange::class
            ]
        ]
    ]
];
?>
