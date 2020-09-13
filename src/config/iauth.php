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
    'units' => [
        'level' => 'h',
        'temp' => 'k',
        'wind' => 'm/s',
    ],
    'values' => [
        'level' => [
            'h_to_ft' => ["0h" => 0, "1000h" => 3.3, "950h" => 20, "925h" => 25, "900h" => 30, "850h" => 50, "800h" => 64, "700h" => 100, "600h" => 140, "500h" => 180, "400h" => 240, "300h" => 300, "200h" => 390, "150h" => 450]
        ]
    ]
];
?>
