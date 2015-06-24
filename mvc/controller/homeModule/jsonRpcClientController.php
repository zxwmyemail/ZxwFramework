<?php

/***********************************************************************************************
| 测试jsonRpc , 此类为客户端示例
***********************************************************************************************/

class jsonRpcClientController extends Controller {
    
    public function index(){
        
        $client = $this->jsonRPCClient;
        $client->setJsonRPCServerUrl('http://localhost/index.php?c=jsonRpcServer&a=index');
        $result = $client->index();
        var_dump($result); // 结果：Hello, JsonRPC!
        $result = $client->test('ZxwFramework');
        var_dump($result); // 结果：Hello, ZxwFramework!
        
    }
    
}

?>
