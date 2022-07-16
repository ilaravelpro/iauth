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
        'password' => [
            'status' => true,
            'types' => ['security', 'login'],
            'password' => [
                'status' => true,
                'before' => true,
            ]
        ],
        'mobile' => [
            'status' => true,
            'one' => true,
            'second_bridges' => ['email'],
            'password' => [
                'status' => true,
                'before' => true,
                'after' => true,
                'type' => 'security',
            ]
        ],
        'email' => [
            'status' => true,
            'one' => true,
            'second_bridges' => ['mobile'],
            'password' => [
                'status' => true,
                'before' => true,
                'after' => true,
                'type' => 'security',
            ]
        ],
        'google_authenticator' => [
            'title' => env('APP_NAME'),
            'status' => true,
            'type' => 'code',
        ],
        'google_authenticator_register' => [
            'status' => true,
            'one' => true,
            'type' => 'code',
            'password' => [
                'status' => true,
                'before' => true,
                'after' => true,
                'type' => 'security',
            ]
        ],
        'any' => [
            'status' => true,
            'password' => [
                'status' => true,
                'before' => true,
                'after' => true,
                'type' => 'security',
            ]
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
                'sessions' => ['auth', 'register', 'recovery', 'password', 'mobile', 'mobile_change', 'login_policy']
            ],
            'email' => [
                'status' => true,
                'sessions' => ['auth', 'register', 'recovery', 'password', 'email', 'email_change', 'login_policy']
            ],
            'google' => [
                'status' => true,
                'sessions' => ['google_register', 'googler_auther', 'recovery', 'password', 'auth', 'login_policy'],
                'field' => 'google_authenticator_secret'
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
            'password' => [
                'title' => 'Password Change',
                'message' => 'password change',
                'model' => \iLaravel\iAuth\Vendor\AuthSession\Password::class
            ],
            'login_policy' => [
                'title' => 'Login Policy',
                'message' => 'login policy',
                'model' => \iLaravel\iAuth\Vendor\AuthSession\LoginPolicy::class
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
                'title' => 'Mobile verification',
                'message' => 'mobile verification',
                'model' => \iLaravel\iAuth\Vendor\AuthSession\Mobile::class
            ],
            'mobile_change' => [
                'title' => 'Mobile change',
                'message' => 'mobile change',
                'model' => \iLaravel\iAuth\Vendor\AuthSession\MobileChange::class
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
            'any' => [
                'title' => 'verification',
                'message' => 'verification',
                'model' => \iLaravel\iAuth\Vendor\AuthSession\Any::class
            ],
        ]
    ]
];
?>
