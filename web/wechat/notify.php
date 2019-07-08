<?php
/**
 * 原生支付、扫码支付及公众号支付的异步回调通知
 * 说明：需要在native.php或者jsapi.php中的填写回调地址，指向本页面
 * 付款成功后，微信服务器会将付款结果通知到该页面
 */
define('DS', DIRECTORY_SEPARATOR);

define('BASE_PATH', dirname(dirname(dirname(__FILE__))));

//加载系统常量
require BASE_PATH . DS . 'conf' . DS . 'const.php';

//Autoload自动载入
require BASE_PATH . DS . 'core' . DS . 'vendor' . DS . 'autoload.php';

header('Content-type:text/html; Charset=utf-8');
$wxNotify = new core\extend\wechat\WxNotify();
$result = $wxNotify->notify();
if($result){
    //完成你的逻辑
    //例如连接数据库，获取付款金额$result['cash_fee']，获取订单号$result['out_trade_no']，修改数据库中的订单状态等;
    //导向控制层，如header('Location: ?r=home.paySuccess');或者直接输出结构，如下：
    echo 'pay success';
}else{
    echo 'pay error';
}
