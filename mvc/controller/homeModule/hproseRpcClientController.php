<?php
if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');
/***********************************************************************************************
| 测试hproseRpc , 此类为客户端示例
***********************************************************************************************/

class hproseRpcClientController extends Controller {
    
    public function index(){

        $this->hproseRPCClient->useService('http://localhost/index.php?c=hproseRpcServer&a=index');
        $result = $this->hproseRPCClient->index();
        var_dump($result); // 结果：Hello, hproseRpc!
        
    }
    
}

?>
