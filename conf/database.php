<?php
/********************************************************************************************
 * 数据库配置文件
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

return [
    'default' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'port'      => '3306',
        'database'  => 'zxw_test',
        'username'  => 'root',
        'password'  => 'ello-admin@163.com',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
        'prefix'    => ''
    ],
    'slave' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'port'      => '3306',
        'database'  => 'zxw_test',
        'username'  => 'root',
        'password'  => 'ello-admin@163.com',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
        'prefix'    => ''
    ],
];