<?php
if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');
/***********************************************************************************************
| 测试phpRpc , 此类为客户端示例
***********************************************************************************************/

class phpRpcClientController extends Controller {
    
    public function index(){
        
        $this->phpRPCClient->useService('http://localhost/index.php?c=phpRpcServer&a=index');
        $result = $this->phpRPCClient->index();
        var_dump($result); // 结果：Hello, phpRPC!
        
    }
    
}

?>
