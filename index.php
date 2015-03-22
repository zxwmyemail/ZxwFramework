<?php

/****************************************************************
 * 应用入口文件
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ****************************************************************/

define('BASE_PATH', dirname(__FILE__));

require BASE_PATH.'/config/const.config.php';

require BASE_PATH.'/config/params.config.php';

require BASE_PATH.'/system/Application.php';

Application::run($_CONFIG);



