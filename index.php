<?php

/****************************************************************
 * 应用入口文件
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 *****************************************************************/
 
define('BASE_PATH', dirname(__FILE__));

//加载系统常量
require BASE_PATH.'/config/const.config.php';

//加载系统配置参数
require BASE_PATH.'/config/params.config.php';

//加载系统入口应用类
require BASE_PATH.'/system/Application.php';

//加载smarty模板类
require BASE_PATH.'/system/framework/smarty/libs/Smarty.php';

Application::run($_CONFIG);


