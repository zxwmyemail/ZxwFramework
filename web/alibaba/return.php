<?php
/**
 * 付款成功后，同步回调地址，主要告知前端用户，付款是否成功
 */
define('DS', DIRECTORY_SEPARATOR);

define('BASE_PATH', dirname(dirname(dirname(__FILE__))));

//加载系统常量
require BASE_PATH . DS . 'conf' . DS . 'const.php';

//Autoload自动载入
require BASE_PATH . DS . 'core' . DS . 'vendor' . DS . 'autoload.php';

header('Content-type:text/html; Charset=utf-8');
$aliNotify = new core\extend\alibaba\AliNotify();
$result = $aliNotify->notify($_GET);
if($result){
	//同步回调一般不处理业务逻辑，显示一个付款成功的页面，或者跳转到用户的财务记录页面即可。
    echo '<h1>付款成功</h1>';
} else {
    echo '<h1>不合法的请求</h1>';
}
exit();
