<?php

/***********************************************************************************************
| 测试hproseRpc , 此类为服务端示例
***********************************************************************************************/

class hproseRpcServerController extends HproseRpcController {

	protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;
    
    public function index(){
        return 'Hello, hproseRpc!';
    }
}


?>
