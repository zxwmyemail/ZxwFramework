<?php

/*************************************************************************************************** 
| Hprose控制器类 (rpc用)
| Copyright (c) 2014-2018 
| Date  2015-06-24 
| Author: iProg <zxwmyemail@163.com>
***************************************************************************************************/

class HproseRpcController {

    protected $allowMethodList  =   '';
    protected $crossDomain      =   false;
    protected $P3P              =   false;
    protected $get              =   true;
    protected $debug            =   false;

    /*---------------------------------------------------------------------------------------------
    | 构造函数
    ----------------------------------------------------------------------------------------------*/
    public function __construct() {

        //控制器初始化
        if(method_exists($this,'_initialize')){
            $this->_initialize();
        }

        //实例化HproseHttpServer
        $server = Application::newObject('HproseHttpServer', 'hproseRPC');

        if($this->allowMethodList){
            $methods    =   $this->allowMethodList;
        }else{
            $methods    =   get_class_methods($this);
            $methods    =   array_diff($methods,array('__construct','__call','_initialize'));   
        }
        $server->addMethods($methods,$this);
        if(CUR_ENV == 'development' || $this->debug ) {
            $server->setDebugEnabled(true);
        }
        // Hprose设置
        $server->setCrossDomainEnabled($this->crossDomain);
        $server->setP3PEnabled($this->P3P);
        $server->setGetEnabled($this->get);
        // 启动server
        $server->start();
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
