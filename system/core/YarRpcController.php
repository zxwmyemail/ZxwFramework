<?php
if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');
/*************************************************************************************************** 
| YarRPC控制器类
|
| 要使用Yar支持首先需要安装Yar扩展
| 扩展下载地址： http://pecl.php.net/package/yar
| Yar说明文档：  http://hk2.php.net/manual/zh/book.yar.php
|
| Copyright (c) 2014-2018 
| Date  2015-06-24 
| Author: iProg <zxwmyemail@163.com>
***************************************************************************************************/

class YarRpcController {

    /*---------------------------------------------------------------------------------------------
    | 构造函数
    ----------------------------------------------------------------------------------------------*/
    public function __construct() {

        //控制器初始化
        if(method_exists($this,'_initialize')) {
           $this->_initialize(); 
        }
            
        //判断扩展是否存在
        if(!extension_loaded('yar'))
            exit('yar扩展加载不成功！');

        //实例化Yar_Server
        $server = new Yar_Server($this);
        // 启动server
        $server->handle();
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
