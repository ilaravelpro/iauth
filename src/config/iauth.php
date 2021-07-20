<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 9:21 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

return [
    'routes' => [
        'api' => [
            'status' => true,
            'sessions' => [
                'status' => true,
                'revoke' => ['status' => true],
                'store' => ['status' => true],
                'verify' => ['status' => true],
                'resend' => ['status' => true],
            ],

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
            'type' => 'code',
            'register' => true,
            'password' => [
                'status' => false,
                'before' => false,
                'after' => false,
            ]
        ],
        'register' => [
            'status' => true,
            'auto' => [
                'status' => true,
            ],
        ],
        'verify' => [
            'status' => true,
            'auto' => true,
            'mode' => 'smart',
            'never' => [],
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
            'time' => 10,
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
            'count' => 5
        ],
        'models' => [
            'auth' => [
                'title' => 'Login to account',
                'message' => 'account login',
                'model' => \iLaravel\iAuth\Vendor\AuthSession\Auth::class
            ],
            'recovery' => [
                'title' => 'Password recovery',
                'message' => 'password recovery',
                'model' => \iLaravel\iAuth\Vendor\AuthSession\Recovery::class
            ],
            'email' => [
                'title' => 'Mobile number verification',
                'message' => 'mobile number verification',
                'model' => \iLaravel\iAuth\Vendor\AuthSession\Email::class
            ],
            'email_change' => [
                'title' => 'Mobile number change',
                'message' => 'mobile number change',
                'model' => \iLaravel\iAuth\Vendor\AuthSession\EmailChange::class
            ],
            'mobile' => [
                'title' => 'Email verification',
                'message' => 'email verification',
                'model' => \iLaravel\iAuth\Vendor\AuthSession\Mobile::class
            ],
            'mobile_change' => [
                'title' => 'Email change',
                'message' => 'email change',
                'model' => \iLaravel\iAuth\Vendor\AuthSession\MobileChange::class
            ]
        ]
    ]
];
?>
