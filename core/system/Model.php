<?php
/*******************************************************************************************
 * 核心model层父类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 *******************************************************************************************/
namespace core\system;

use core\extend\monolog\Log;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model as IlluminateModel;

class Model extends IlluminateModel {
    use Common;
}

?>


