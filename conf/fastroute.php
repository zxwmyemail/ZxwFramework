<?php
/********************************************************************************************
 * Fast-Route 路由配置文件
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

return [
    ['method' => 'GET', 'uri' => '/home', 'ctrl' => 'app\home\controllers\Home@index'],
    ['method' => 'GET', 'uri' => '/home/info', 'ctrl' => 'app\home\controllers\Home@phpInfo'],
];

