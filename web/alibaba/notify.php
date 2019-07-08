<?php
/**
 * 付款成功后，异步回调地址，主要告知阿里服务器，是否成功接收到阿里的支付结果数据
 */
define('DS', DIRECTORY_SEPARATOR);

define('BASE_PATH', dirname(dirname(dirname(__FILE__))));

//加载系统常量
require BASE_PATH . DS . 'conf' . DS . 'const.php';

//Autoload自动载入
require BASE_PATH . DS . 'core' . DS . 'vendor' . DS . 'autoload.php';

header('Content-type:text/html; Charset=utf-8');
$aliNotify = new core\extend\alibaba\AliNotify();
$result = $aliNotify->notify($_POST);
if($result){
	//处理你的逻辑，例如获取订单号$_POST['out_trade_no']，订单金额$_POST['total_amount']等
    //程序执行完后必须打印输出“success”（不包含引号）。如果商户反馈给支付宝的字符不是success这7个字符，支付宝服务器会不断重发通知，直到超过24小时22分钟。一般情况下，25小时以内完成8次通知（通知的间隔频率一般是：4m,10m,10m,1h,2h,6h,15h）；
    echo 'success';
} else {
    echo 'error';
}
exit();
