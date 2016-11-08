<?php
if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');
/***********************************************************************************************
| 测试yarRpc , 此类为客户端示例
***********************************************************************************************/

class yarRpcClientController extends Controller {
    
    public function index(){

    	$client = new Yar_client('http://localhost/index.php/Home/Server');
        $result = $client->index();
        var_dump($result); // 结果：Hello, yarRPC!
        
    }
    
}

?>
