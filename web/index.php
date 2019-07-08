<?php

define('DS', DIRECTORY_SEPARATOR);

define('BASE_PATH', dirname(dirname(__FILE__)));

//加载系统常量
require BASE_PATH . DS . 'conf' . DS . 'const.php';
// Autoload 自动载入
require BASE_PATH . DS . 'core' . DS . 'vendor' . DS . 'autoload.php';
//启动应用
core\system\Application::bootstrap();