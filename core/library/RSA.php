<?php
namespace core\library;

class RSA {

    /**
     * 构造函数
     */
    public function __construct() {}

    /**
     * 生成一对公私钥 成功返回 公私钥数组 失败 返回 false
     */
    public static function createRSAKey() {
        $config = [
            'private_key_bits' => 1024,
            'config' => 'D:\phpStudy\PHPTutorial\Apache\conf\openssl.cnf'
        ];
        $res = openssl_pkey_new($config);
        if($res == false) return false;
        openssl_pkey_export($res, $privateKey, null, $config);
        $publicKey = openssl_pkey_get_details($res);
        
        return [
            'public_key'  => $publicKey["key"],
            'private_key' => $privateKey
        ];
    }

    /**
     * 重新格式化,为保证任何key都可以识别
     */
    public static function getPemKey($keyPath, $type){
        $search = [];
        if ($type == 'public') {
            $search = [
                "-----BEGIN PUBLIC KEY-----",
                "-----END PUBLIC KEY-----",
                "\n",
                "\r",
                "\r\n"
            ];
        } else if ($type == 'private') {
            $search = [
                "-----BEGIN PRIVATE KEY-----",
                "-----END PRIVATE KEY-----",
                "\n",
                "\r",
                "\r\n"
            ];
        } else {
            return false;
        }

        if (!file_exists($keyPath)) return false;

        $keyContent = file_get_contents($keyPath);
        $keyContent = str_replace($search, "", $keyContent);
        return $search[0] . PHP_EOL . wordwrap($keyContent, 64, "\n", true) . PHP_EOL . $search[1] . PHP_EOL;
    }


    /**
     * RSA加签
     * @param $paramStr
     * @param $priKey
     * @return string
     */
    public static function sign($paramStr, $priKey) {
        $sign = '';

        //将字符串格式公私钥转为pem格式公私钥
        $priKeyPem = self::getPemKey($priKey, 'private');

        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($priKeyPem);

        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($paramStr, $sign, $res);

        //释放资源
        openssl_free_key($res);

        //base64编码签名
        $sign = base64_encode($sign);

        //url编码签名
        $sign = urlencode($sign);

        return $sign;
    }

    /**
     * RSA验签
     * @param $paramStr
     * @param $sign
     * @param $pubKey
     * @return bool
     */
    public static function verify($paramStr, $sign, $pubKey)  {
        //将字符串格式公私钥转为pem格式公私钥
        $pubKeyPem = self::getPemKey($pubKey, 'public');

        //转换为openssl密钥，必须是没有经过pkcs8转换的公钥
        $res = openssl_get_publickey($pubKeyPem);

        //base64解码签名
        $signBase64 = base64_decode(urldecode($sign));

        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($paramStr, $signBase64, $res);

        //释放资源
        openssl_free_key($res);

        //返回资源是否成功
        return $result;
    }

}
