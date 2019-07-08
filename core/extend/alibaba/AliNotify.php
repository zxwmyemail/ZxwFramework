<?php
namespace core\extend\wechat;
/********************************************************************************************
 微信通知类
*********************************************************************************************/
use core\extend\monolog\Log;

class AliNotify extends Alibaba {

    public function __construct($whichConf = 'default') {
        parent::__construct($whichConf);
    }

    /**
     *  验证签名
     **/
    public function notify($params) {
        $sign     = $params['sign'];
        $signType = $params['sign_type'];
        unset($params['sign_type']);
        unset($params['sign']);
        return $this->verify($params, $sign, $signType);
    }

}
