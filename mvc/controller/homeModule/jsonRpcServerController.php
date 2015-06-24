<?php

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
