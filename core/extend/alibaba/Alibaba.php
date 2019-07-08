<?php
namespace core\extend\wechat;
/********************************************************************************************
 微信父类
*********************************************************************************************/
use core\system\Config;
use core\library\HttpCurl;
use core\system\Common;

class Alibaba {
    use Common;

    public $_aliConf;

    const ALIPAY_GATEWAY_URL = 'https://openapi.alipay.com/gateway.do';
    const AUTHORIZE_URL      = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm';

    public function __construct($whichConf = 'default') {
        $this->_aliConf = Config::get('alibaba', $whichConf);
    }

    /**
     * 电脑pc支付，构建请求，以表单HTML形式构造（默认）
     * @param $paramsTemp 请求参数数组
     * @return 提交表单HTML文本
     */
    public function buildRequestForm($paramsTemp) {
        $config = $this->_aliConf;
        $actionUrl = self::ALIPAY_GATEWAY_URL . '?charset=' . $config['charset'];
        $sHtml  = '请稍等，正在跳转至支付页面...';
        $sHtml .= '<form id="alipaysubmit" name="alipaysubmit" action="'.$actionUrl.'" method="POST">';
        foreach($paramsTemp as $key => $val){
            if (!empty($val)) {
                $val = str_replace("'", "&apos;", $val);
                $sHtml .= "<input type='hidden' name='".$key."' value='".$val."'/>";
            }   
        }
        //submit按钮控件请不要含有name属性
        $sHtml .= "<input type='submit' value='ok' style='display:none;'></form>";
        $sHtml .= "<script>document.forms['alipaysubmit'].submit();</script>";
        return $sHtml;
    }

    /**
     * 生成验签签名
     */
    public function getSign($params) {
        $config = $this->_aliConf;

        // 获取商户私钥
        $res  = "-----BEGIN RSA PRIVATE KEY-----\n";
        $res .= wordwrap($config['rsaPrivateKey'], 64, "\n", true);
        $res .= "\n-----END RSA PRIVATE KEY-----";

        $sign = '';
        $data = $this->getSignStr($params, $config['charset']);
        if ("RSA2" == $config['signType']) {
            //OPENSSL_ALGO_SHA256是php5.4.8以上版本才支持
            openssl_sign($data, $sign, $res, version_compare(PHP_VERSION,'5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256); 
        } else {
            openssl_sign($data, $sign, $res);
        }

        return base64_encode($sign);
    }

    /**
     * 利用公钥对验签进行校验
     */
    public function verify($params, $sign, $signType) {
        $config = $this->_aliConf;

        $pubKey= $this->alipayPublicKey;
        $res  = "-----BEGIN PUBLIC KEY-----\n" .
        $res .= wordwrap($config['alipayPublicKey'], 64, "\n", true) .
        $res .= "\n-----END PUBLIC KEY-----";

        $data = $this->getSignStr($params, $config['charset']);

        //调用openssl内置方法验签，返回bool值
        if ("RSA2" == $signType) {
            return (bool)openssl_verify($data, base64_decode($sign), $res, version_compare(PHP_VERSION,'5.4.0', '<') ? SHA256 : OPENSSL_ALGO_SHA256);
        } 
            
        return (bool)openssl_verify($data, base64_decode($sign), $res);
    }

    /**
     * 生成验签字符串
     */
    public function getSignStr($params, $characet) {
        $i = 0;
        ksort($params);
        $signStr = "";
        foreach ($params as $k => $v) {
            if (!empty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->characet($v, $characet);
                if ($i == 0) {
                    $signStr .= "$k" . "=" . "$v";
                } else {
                    $signStr .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $signStr;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    public function characet($data, $targetCharset) {
        if (!empty($data)) {
            $fileType = $this->charset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }
        return $data;
    }


    /**
     * 拼接签名字符串
     * @param array $urlObj
     * @return 返回已经拼接好的字符串
     */
    public function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign") $buff .= $k . "=" . $v . "&";
        }
        $buff = trim($buff, "&");
        return $buff;
    }

}

