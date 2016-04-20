<?php

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
