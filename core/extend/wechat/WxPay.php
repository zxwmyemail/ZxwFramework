<?php
namespace core\extend\wechat;
/********************************************************************************************
 微信支付类
 使用方法，见document.txt文件
*********************************************************************************************/
use core\library\HttpCurl;
use core\extend\monolog\Log;

class WxPay extends Wechat {

    const REFUND_URL          = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
    const REVERSE_URL         = 'https://api.mch.weixin.qq.com/secapi/pay/reverse';
    const REFUND_QUERY_URL    = 'https://api.mch.weixin.qq.com/pay/refundquery';
    const ORDER_QUERY_URL     = 'https://api.mch.weixin.qq.com/pay/orderquery';
    const UNIFIE_ORDER_URL    = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    const MICRO_PAY_URL       = 'https://api.mch.weixin.qq.com/pay/micropay';
    const RED_PACKET_URL      = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
    const TRANSFERS_ORDER_URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

    public function __construct($whichConf = 'default') {
        parent::__construct($whichConf);
    }

    /**
     * 发起native订单（原生扫码支付订单）
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $timestamp 订单发起时间
     * @return array
     */
    public function createNativeOrder($totalFee, $outTradeNo, $orderName, $timestamp)
    {
        $config = $this->_wxConfig;
        $unified = [
            'appid'            => $config['appId'],
            'attach'           => 'pay',             //商家数据包，原样返回，如果填写中文，请注意转换为utf-8
            'body'             => iconv('GBK','UTF-8',$orderName),
            'mch_id'           => $config['mchid'],
            'nonce_str'        => $this->createNonceStr(),
            'notify_url'       => $config['notifyUrl'],
            'out_trade_no'     => $outTradeNo,
            'spbill_create_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            'total_fee'        => intval($totalFee * 100),       //单位 转为分
            'trade_type'       => 'NATIVE',
        ];
        $unified['sign'] = $this->getSign($unified, $config['apiKey']);
        $responseXml = HttpCurl::post(self::UNIFIE_ORDER_URL, $this->arrayToXml($unified));
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);        
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false || $unifiedOrder->return_code != 'SUCCESS' || $unifiedOrder->result_code != 'SUCCESS') {
            $log = Log::getInstance(); 
            $log->error(print_r($responseXml, true));
            return false;
        }

        $codeUrl = (array)($unifiedOrder->code_url);
        if(!$codeUrl[0]) {
            $log = Log::getInstance(); 
            $log->error('get code_url error');
            return false;
        }

        $result = [
            "appId"     => $config['appId'],
            "timeStamp" => $timestamp,
            "nonceStr"  => $this->createNonceStr(),
            "package"   => "prepay_id=" . $unifiedOrder->prepay_id,
            "signType"  => 'MD5',
            "code_url"  => $codeUrl[0],
        ];
        $result['paySign'] = $this->getSign($result, $config['apiKey']);
        return $result;
    }

    /**
     * 发起jsapi订单（公众号支付订单）
     * @param string $openid 调用【网页授权获取用户信息】接口获取到用户在该公众号下的Openid
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $timestamp 支付时间
     * @return array
     */
    public function createJsApiOrder($openid, $totalFee, $outTradeNo, $orderName, $timestamp)
    {
        $config = $this->_wxConfig;
        $unified = [
            'appid'            => $config['appId'],
            'attach'           => 'pay',             //商家数据包，原样返回，如果填写中文，请注意转换为utf-8
            'body'             => iconv('GBK','UTF-8',$orderName),
            'mch_id'           => $config['mchid'],
            'nonce_str'        => $this->createNonceStr(),
            'notify_url'       => $config['notifyUrl'],
            'openid'           => $openid,   
            'out_trade_no'     => $outTradeNo,
            'spbill_create_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            'total_fee'        => intval($totalFee * 100),       //单位 转为分
            'trade_type'       => 'JSAPI',
        ];
        $unified['sign'] = $this->getSign($unified, $config['apiKey']);
        $responseXml = HttpCurl::post(self::UNIFIE_ORDER_URL, $this->arrayToXml($unified));
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);     
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false || $unifiedOrder->return_code != 'SUCCESS' || $unifiedOrder->result_code != 'SUCCESS') {
            $log = Log::getInstance(); 
            $log->error(print_r($responseXml, true));
            return false;
        }

        $result = [
            "appId"     => $config['appId'],
            "timeStamp" => "$timestamp",        //这里是字符串的时间戳，不是int，所以需加引号
            "nonceStr"  => $this->createNonceStr(),
            "package"   => "prepay_id=" . $unifiedOrder->prepay_id,
            "signType"  => 'MD5',
        ];
        $result['paySign'] = $this->getSign($result, $config['apiKey']);
        return json_encode($result);
    }

    /**
     * 发起native订单（原生扫码支付订单）
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @return array
     */
    public function createH5Order($totalFee, $outTradeNo, $orderName)
    {
        $config = $this->_wxConfig;
        $sceneInfo = [
            'h5_info' => [
                'type'    => 'Wap',
                'wap_url' => $config['h5WapUrl'],
                'wap_name'=> $config['h5WapName'],
            ]
        ];
        $unified = [
            'appid'            => $config['appId'],
            'attach'           => 'pay',             //商家数据包，原样返回，如果填写中文，请注意转换为utf-8
            'body'             => $orderName,
            'mch_id'           => $config['mchid'],
            'nonce_str'        => $this->createNonceStr(),
            'notify_url'       => $config['notifyUrl'],
            'out_trade_no'     => $outTradeNo,
            'spbill_create_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            'total_fee'        => intval($totalFee * 100),       //转为分
            'trade_type'       => 'MWEB',
            'scene_info'       => json_encode($sceneInfo)
        ];
        $unified['sign'] = $this->getSign($unified, $config['apiKey']);
        $responseXml = HttpCurl::post(self::UNIFIE_ORDER_URL, $this->arrayToXml($unified));
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false || $unifiedOrder->return_code != 'SUCCESS') {
            $log = Log::getInstance(); 
            $log->error(print_r($responseXml, true));
            return false;
        }

        if($unifiedOrder->mweb_url){
            return $unifiedOrder->mweb_url . '&redirect_url=' . urlencode($config['h5ReturnUrl']);
        }
        return false;
    }

    /**
     * 发起企业付款（即转账）订单
     * @param string $openId 用户openid
     * @param float  $payAmount 转账金额，单位:元（转账最小金额为1元）
     * @param string $outTradeNo 唯一的订单号
     * @param string $trueName 收款人真实姓名
     * @return array
     */
    public function createEnterpriseOrder($openId, $totalFee, $outTradeNo, $trueName)
    {
        $config = $this->_wxConfig;
        $unified = array(
            'mch_appid'        => $config['appId'],
            'mchid'            => $config['mchid'],
            'nonce_str'        => $this->createNonceStr(),
            'openid'           => $openId,
            'check_name'       => 'FORCE_CHECK',             //NO_CHECK：不校验真实姓名，FORCE_CHECK：强校验真实姓名
            're_user_name'     => $trueName,                 //收款用户真实姓名（不支持给非实名用户打款）
            'partner_trade_no' => $outTradeNo,
            'spbill_create_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            'amount'           => intval($totalFee * 100),   //单位 转为分
            'desc'             =>'付款',                     //企业付款操作说明信息
        );
        $unified['sign'] = $this->getSign($unified, $config['apiKey']);
        $responseXml = $this->curlSslPost(
            self::TRANSFERS_ORDER_URL, $this->arrayToXml($unified), $config['apiclientCert'], $config['apiclientKey']
        );
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false || $unifiedOrder->return_code != 'SUCCESS' || $unifiedOrder->result_code != 'SUCCESS') {
            $log = Log::getInstance(); 
            $log->error(print_r($responseXml, true));
            return false;
        }
        return true;
    }

    /**
     * 发起订单
     * @param float  $payAmount 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $authCode 用户付款码
     * @param string $deviceInfo 终端设备号(商户自定义，如门店编号)
     * @return array
     */
    public function createBarCodeOrder($outTradeNo, $orderName, $payAmount, $authCode, $deviceInfo = '')
    {
        $config = $this->_wxConfig;
        $unified = [
            'appid'            => $config['appId'],
            'attach'           => 'pay',             //商家数据包，原样返回，如果填写中文，请注意转换为utf-8
            'body'             => iconv('GBK', 'UTF-8', $orderName),
            'mch_id'           => $config['mchid'],
            'nonce_str'        => $this->createNonceStr(),
            'out_trade_no'     => $outTradeNo,
            'spbill_create_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            'total_fee'        => intval($payAmount * 100),  //单位 转为分
            'auth_code'        => $authCode,                  
            'device_info'      => $deviceInfo,         //终端设备号(商户自定义，如门店编号)
            'limit_pay'        => 'no_credit'          //no_credit:指定不能使用信用卡支付
        ];
        $unified['sign'] = $this->getSign($unified, $config['apiKey']);
        $responseXml = HttpCurl::post(self::MICRO_PAY_URL, $this->arrayToXml($unified));
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false || $unifiedOrder->return_code != 'SUCCESS' || $unifiedOrder->result_code != 'SUCCESS') {
            $log = Log::getInstance(); 
            $log->error(print_r($responseXml, true));
            $log->info('错误码说明：https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_10&index=1#7');
            return false;
        }

        return (array)$unifiedOrder;
    }

    /**
     * 发红包
     * @param string $openid 调用【网页授权获取用户信息】接口获取到用户在公众号下的Openid
     * @param float  $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $sendName  发送者姓名
     * @param string $wishing 祝福语
     * @param string $actName 活动名称
     * @return boolean
     */
    public function handoutRedPacket($openid, $totalFee, $outTradeNo, $sendName, $wishing, $actName)
    {
        $config = $this->_wxConfig;
        $unified = array(
            'wxappid'      => $config['appId'],
            'send_name'    => $sendName,
            'mch_id'       => $config['mchid'],
            'nonce_str'    => $this->createNonceStr(),
            're_openid'    => $openid,
            'mch_billno'   => $outTradeNo,
            'client_ip'    => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            'total_amount' => intval($totalFee * 100),   //单位 转为分
            'total_num'    => 1,                         //红包发放总人数
            'wishing'      => $wishing,                  //红包祝福语
            'act_name'     => $actName,                  //活动名称
            'remark'       => '',                        //备注信息，如为中文注意转为UTF8编码
            'scene_id'     =>'PRODUCT_2',                //发放红包使用场景，红包金额大于200时必传。
        );
        $unified['sign'] = $this->getSign($unified, $config['apiKey']);
        $responseXml = $this->curlSslPost(self::RED_PACKET_URL, $this->arrayToXml($unified), $config['apiclientCert'], $config['apiclientKey']);
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false || $unifiedOrder->return_code != 'SUCCESS' || $unifiedOrder->result_code != 'SUCCESS') {
            $log = Log::getInstance(); 
            $log->error(print_r($responseXml, true));
            return false;
        }
        return true;
    }

    /**
     * 订单查询
     * @param $string $outTradeNo  要查询的订单号
     * @return array 订单信息
     */
    public function orderQuery($outTradeNo)
    {
        $config = $this->_wxConfig;
        $unified = [
            'appid'        => $config['appId'],
            'mch_id'       => $config['mchid'],
            'out_trade_no' => $outTradeNo,
            'nonce_str'    => $this->createNonceStr(),
        ];
        $unified['sign'] = $this->getSign($unified, $config['apiKey']);
        $responseXml = HttpCurl::post(self::ORDER_QUERY_URL, $this->arrayToXml($unified));
        $queryResult = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($queryResult === false || $queryResult->return_code != 'SUCCESS') {
            $log = Log::getInstance(); 
            $log->error(print_r($responseXml, true));
            return false;
        }

        $tradeState = $queryResult->trade_state;
        $data['code']  = $tradeState == 'SUCCESS' ? 0 : 1;
        $data['state'] = $tradeState;
        $data['desc']  = $this->getTradeState($tradeState);
        $data['time']  = date('Y-m-d H:i:s');
        return $data;
    }

    /**
     * 退款
     * @param float  $totalFee   订单金额 单位元
     * @param float  $refundFee  退款金额 单位元
     * @param string $refundNo   退款单号(可自己随机生成唯一的字符串)
     * @param string $wxOrderNo  微信订单号(商户订单号与微信订单号二选一，至少填一个）
     * @param string $orderNo    商户订单号(商户订单号与微信订单号二选一，至少填一个）
     * @return string
     */
    public function orderRefund($totalFee, $refundFee, $refundNo, $wxOrderNo = '', $orderNo = '')
    {
        $config = $this->_wxConfig;
        $unified = [
            'appid'          => $config['appId'],
            'mch_id'         => $config['mchid'],
            'nonce_str'      => $this->createNonceStr(),
            'total_fee'      => intval($totalFee * 100),  //订单金额 单位 转为分
            'refund_fee'     => intval($refundFee * 100), //退款金额 单位 转为分
            'sign_type'      => 'MD5',                    //签名类型 支持HMAC-SHA256和MD5，默认为MD5
            'transaction_id' => $wxOrderNo,               //微信订单号
            'out_trade_no'   => $orderNo,                 //商户订单号
            'out_refund_no'  => $refundNo,                //商户退款单号
            'refund_desc'    => '商品已售完',              //退款原因（选填）
        ];
        $unified['sign'] = $this->getSign($unified, $config['apiKey']);
        $responseXml = $this->curlSslPost(self::REFUND_URL, $this->arrayToXml($unified), $config['apiclientCert'], $config['apiclientKey']);
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false || $unifiedOrder->return_code != 'SUCCESS' || $unifiedOrder->result_code != 'SUCCESS') {
            $log = Log::getInstance(); 
            $log->error(print_r($responseXml, true));
            return false;
        }
        return true;
    }

    /**
     * 退款查询
     * 以下四个单号四选一。查询的优先级是： 微信退款单号 > 商户退款订单号 > 微信订单号 > 商户订单号
     * @param string $refundNo 商户退款单号
     * @param string $wxOrderNo 微信订单号
     * @param string $orderNo 商户订单号
     * @param string $refundId 微信退款单号
     * @return string
     */
    public function refundQuery($refundId = '', $refundNo = '', $wxOrderNo = '', $orderNo = '')
    {
        $config = $this->_wxConfig;
        $unified = [
            'appid'          => $config['appId'],
            'mch_id'         => $config['mchid'],
            'nonce_str'      => $this->createNonceStr(),
            'sign_type'      => 'MD5',           //签名类型 支持HMAC-SHA256和MD5，默认为MD5
            'transaction_id' => $wxOrderNo,      //微信订单号
            'out_trade_no'   => $orderNo,        //商户订单号
            'out_refund_no'  => $refundNo,       //商户退款单号
            'refund_id'      => $refundId,       //微信退款单号
        ];
        $unified['sign'] = $this->getSign($unified, $config['apiKey']);
        $responseXml = $this->curlPost(self::REFUND_QUERY_URL, $this->arrayToXml($unified));
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false || $unifiedOrder->return_code != 'SUCCESS' || $unifiedOrder->result_code != 'SUCCESS') {
            $log = Log::getInstance(); 
            $log->error(print_r($responseXml, true));
            return false;
        }
        return true;
    }

    /**
     * 撤销订单
     * 注意：7天以内的交易单可调用撤销，其他正常支付的单如需实现相同功能请调用申请退款API。
     * 错误码参照：https://pay.weixin.qq.com/wiki/doc/api/micropay.php?chapter=9_11&index=3
     * @param string $transaction_id   //微信的订单号，优先使用。微信订单号与商户订单号不能同时为空
     * @param string $out_trade_no     //商户订单号。微信订单号与商户订单号不能同时为空
     */
    public function orderReverse($transactionId, $outTradeNo)
    {
        $config = $this->_wxConfig;
        $unified = [
            'appid'          => $config['appId'],
            'mch_id'         => $config['mchid'],
            'transaction_id' => $transactionId,
            'out_trade_no'   => $outTradeNo,
            'nonce_str'      => $this->createNonceStr(),
        ];
        $unified['sign'] = $this->getSign($unified, $config['apiKey']);
        $responseXml = $this->curlSslPost(self::REVERSE_URL, $this->arrayToXml($unified), $config['apiclientCert'], $config['apiclientKey']);    
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false || $unifiedOrder->return_code != 'SUCCESS' || $unifiedOrder->result_code != 'SUCCESS') {
            $log = Log::getInstance(); 
            $log->error(print_r($responseXml, true));
            return false;
        }
        return true;
    }

}

