<?php

/***********************************************************************************************
| 测试jsonRpc , 此类为客户端示例
***********************************************************************************************/

class jsonRpcClientController extends Controller {
    
    public function index(){
        // 导入客户端类库
        $client = Application::newObject('jsonRPCClient', 'jsonRPC');
        $client->setJsonRPCServerUrl('http://localhost/index.php?c=jsonRpcServer&a=index');
        $result = $client->index();
        var_dump($result); // 结果：Hello, JsonRPC!
        $result = $client->test('ThinkPHP');
        var_dump($result); // 结果：Hello, ThinkPHP!
    }
    
}

?>