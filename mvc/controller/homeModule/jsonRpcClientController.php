<?php


class jsonRpcClientController extends Controller {
    public function index(){
        vendor('jsonRPC.jsonRPCClient');
        $client = new \jsonRPCClient('http://serverName/index.php/Home/Server');
        $result = $client->index();
        var_dump($result); // 结果：Hello, JsonRPC!
        $result = $client->test('ThinkPHP');
        var_dump($result); // 结果：Hello, ThinkPHP!
    }
}

?>
