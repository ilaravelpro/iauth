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
        'models' => [
            'mobile' => [
                'status' => true,
                'theories' => ['auth', 'register', 'recovery', 'password', 'mobile']
            ],
            'email' => [
                'status' => true,
                'theories' => ['auth', 'register', 'recovery', 'password', 'email']
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
