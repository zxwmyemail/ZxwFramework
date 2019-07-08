<?php
namespace core\extend\wechat;
/********************************************************************************************
 微信通知类
*********************************************************************************************/
use core\extend\monolog\Log;

class WxNotify extends Wechat {

    public function __construct($whichConf = 'default') {
        parent::__construct($whichConf);
    }

    public function notify() {
        $config = $this->_wxConfig;
        $postStr = file_get_contents('php://input');
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);        
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($postObj === false || $postObj->return_code != 'SUCCESS' || $postObj->result_code != 'SUCCESS') {
            $log = Log::getInstance(); 
            $log->error(print_r($responseXml, true));
            return false;
        }

        $arr = (array)$postObj;
        unset($arr['sign']);
        if ($this->getSign($arr, $config['apiKey']) == $postObj->sign) {
            return $arr;
        }
        return false;
    }

}
