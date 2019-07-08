<?php
/********************************************************************************************
 * email配置文件
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

return [
    'default' => [
        'smtp_debug'  => 2,                                      // Enable verbose debug output
        'host'        => 'smtp.163.com',                         // Set mailer to use SMTP
        'smtp_auth'   => true,                                   // Enable SMTP authentication
        'username'    => 'zxwmyemail@163.com',                   // SMTP username
        'password'    => 'iprog659',                             // SMTP password
        'smtp_secure' => 'ssl',                                  // Enable TLS encryption, `ssl` also accepted
        'port'        => 465,                                    // SMTP password
    ],
];

