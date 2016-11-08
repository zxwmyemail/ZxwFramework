<?php
if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');
/***********************************************************************************************
| 测试jsonRpc , 此类为服务端示例
***********************************************************************************************/

class jsonRpcServerController extends JsonRpcController {
    public function index(){
        return 'Hello, JsonRPC!';
    }
    // 支持参数传入
    public function test($name=''){
        return "Hello, {$name}!";
    }
}


?>
