<?php
if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');
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
