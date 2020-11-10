<?php
return [
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => 'root',
        'database' => 0,
        /**连接池设置**/
        //连接池的最大连接数量
        'max_num' => 5,
        'min_num' => 1,
    ]
];
