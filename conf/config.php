<?php
/********************************************************************************************
 * 系统配置文件
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 *
 * 当route端的use_pathinfo = 2时，为pathinfo模式，
 * 静态文件的相对路径失效，此时可考虑使用绝对路径，然后使用nginx，将静态文件请求定位到该绝对路径，比如：
 * 将相对路径
 * <link rel="stylesheet" type="text/css" href="asset/css/404.css">
 * 改为绝对路径：
 * <link rel="stylesheet" type="text/css" href="/asset/css/404.css">
 *
 * 然后nginx中server段配置如下代码：
 * location /asset/ {
 *      alias /path/to/asset/;
 * }
 *
 * 然后apache中配置的如下：
 * <IfModule alias_module>
 *    Alias /asset/  D:\WorkSpace\www\myframe\web\asset\
 * </IfModule>
 ********************************************************************************************/

return [
    'route' => [
    	// 是否开启FastRoute路由，开启后，下面的use_pathinfo的两个模式失效
    	'open_fastroute'     => false,
    	// 1为common模式    index.php?m=module&r=controller.action&id=2
        // 2为pathinfo模式  index.php/module/controller/action/?id=2
    	'use_pathinfo'       => 1,
        'default_module'     => 'home',
        'default_controller' => 'home', 
        'default_action'     => 'index',                                                                          
    ],
];