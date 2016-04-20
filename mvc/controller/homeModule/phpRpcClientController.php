<?php

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
