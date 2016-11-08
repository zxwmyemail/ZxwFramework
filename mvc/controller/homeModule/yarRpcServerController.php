<?php
if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');
/***********************************************************************************************
| 测试yarRpc , 此类为服务端示例
***********************************************************************************************/

class yarRpcServerController extends YarRpcController {
    public function index(){
        return 'Hello, yarRPC!';
    }
}


?>
