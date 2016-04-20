<?php

/*************************************************************************************************** 
| PhpRPC控制器类
| Copyright (c) 2014-2018 
| Date  2015-06-24 
| Author: iProg <zxwmyemail@163.com>
***************************************************************************************************/

class PhpRpcController {

    protected $allowMethodList  =   '';
    protected $debug            =   false;

    /*---------------------------------------------------------------------------------------------
    | 构造函数
    ----------------------------------------------------------------------------------------------*/
    public function __construct() {

        //控制器初始化
        if(method_exists($this,'_initialize')){
            $this->_initialize();
        }
            
        // 导入类库
        Application::newObject('phprpc_Server', 'phpRPC', 'static');

        //实例化phprpc
        $server     =   new PHPRPC_Server();

        if($this->allowMethodList){
            $methods    =   $this->allowMethodList;
        }else{
            $methods    =   get_class_methods($this);
            $methods    =   array_diff($methods,array('__construct','__call','_initialize'));   
        }
        $server->add($methods,$this);

        if(CUR_ENV == 'development' || $this->debug ) {
            $server->setDebugMode(true);
        }
        $server->setEnableGZIP(true);
        $server->start();
        echo $server->comment();
    }

    /*---------------------------------------------------------------------------------------------
    | 魔术方法 有不存在的操作的时候执行
    |----------------------------------------------------------------------------------------------
    | @access public
    | @param string $method 方法名
    | @param array $args 参数
    |
    | @return mixed
    ----------------------------------------------------------------------------------------------*/
    public function __call($method,$args){}
}
