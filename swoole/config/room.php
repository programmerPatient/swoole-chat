<?php
return [
    'connection' => [
        'host' => '0.0.0.0',
        'port' => 9501,
        'set' => [
            'worker_num' => 2,
            'daemonize' => 1,
        ]
    ],
    'innerServer' => [
        'host' => '127.0.0.1',
        'port' => 9511,
        'set' => [
        ]
    ]
];
