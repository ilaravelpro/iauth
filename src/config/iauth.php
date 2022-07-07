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
    "tester" => [
        "username" => [
            "google" => false,
            "apple" => false,
        ],
        "code" => 485251
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
        'google_authenticator' => [
            'title' => env('APP_NAME'),
            'status' => true,
            'type' => 'code',
        ],
        'google_authenticator_register' => [
            'status' => true,
            'type' => 'code',
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
            'google' => [
                'status' => true,
                'sessions' => ['google_register', 'googler_auther', 'mobile_change', 'email_change', 'mobile', 'email', 'recovery', 'password']
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
            'google_authenticator_register' => [
                'title' => 'Google authenticator register',
                'message' => 'google authenticator register',
                'model' => \iLaravel\iAuth\Vendor\AuthSession\GoogleAuthenticatorRegister::class
            ],
            'google_authenticator' => [
                'title' => 'Google authenticator verification',
                'message' => 'google authenticator verification',
                'model' => \iLaravel\iAuth\Vendor\AuthSession\GoogleAuthenticator::class
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
