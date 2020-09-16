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
            'mode' => 'smart',
            'ever' => true,
            'other' => 'email'
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
        'mobile' => [
            'status' => true,
            'theories' => ['auth', 'register', 'recovery', 'password', 'mobile']
        ],
        'email' => [
            'status' => true,
            'theories' => ['auth', 'register', 'recovery', 'password', 'email']
        ]
    ],
    'theories' => [
        'auth' => [
            'status' => true,
            'model' => \iLaravel\iAuth\Vendor\AuthTheory\Auth::class,
        ],
        'register' => [
            'status' => true,
            'model' => \iLaravel\iAuth\Vendor\AuthTheory\Register::class,
        ],
        'recovery' => [
            'status' => true,
            'model' => \iLaravel\iAuth\Vendor\AuthTheory\Recovery::class,
        ],
        'password' => [
            'status' => true,
            'model' => \iLaravel\iAuth\Vendor\AuthTheory\Password::class,
        ],
        'mobile' => [
            'status' => true,
            'model' => \iLaravel\iAuth\Vendor\AuthTheory\Mobile::class,
        ],
        'email' => [
            'status' => true,
            'model' => \iLaravel\iAuth\Vendor\AuthTheory\Email::class,
        ],
    ]
];
?>
