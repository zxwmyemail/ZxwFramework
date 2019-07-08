<?php
namespace core\extend\wechat;
/********************************************************************************************
 微信父类
*********************************************************************************************/
use core\system\Config;
use core\library\HttpCurl;
use core\system\Common;

class Wechat {
    use Common;

    public $_wxConfig;
    const TOKEN_URL  = 'https://api.weixin.qq.com/cgi-bin/token';
    const TICKET_URL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';

    public function __construct($whichConf = 'default') {
        $this->_wxConfig = Config::get('wechat', $whichConf);
    }

    /**
     * 订单交易状态
     * @param $string $str  状态
     */
    public function getTradeState($str) {
        switch ($str) {
            case 'SUCCESS';
                return '支付成功';
            case 'REFUND';
                return '转入退款';
            case 'NOTPAY';
                return '未支付';
            case 'CLOSED';
                return '已关闭';
            case 'REVOKED';
                return '已撤销（刷卡支付）';
            case 'USERPAYING';
                return '用户支付中';
            case 'PAYERROR';
                return '支付失败';
        }
    }

    /**
     * 拼接签名字符串
     * @param array $urlObj
     * @return 返回已经拼接好的字符串
     */
    public function toUrlParams($urlObj) {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if($k != "sign") $buff .= $k . "=" . $v . "&";
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 将数组拼接为xml
     * @param array $arr
     * @return 返回已经拼接好的字符串
     */
    public function arrayToXml($arr) {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 获取参数签名
     * @param array $arr
     * @return 返回已经拼接好的字符串
     */
    public function getSign($params, $key) {
        ksort($params, SORT_STRING);
        $unSignParaString = $this->formatQueryParams($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }

    /**
     * 转换参数数组为字符串
     * @param array $paraMap   参数数组
     * @return 返回已经拼接好的字符串
     */
    public function formatQueryParams($paraMap, $urlEncode = false) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    public function createNonceStr($length = 16) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public function getJsApiTicket() {
        $ticket = '';
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $filePath = CORE_PATH . '/extend/wechat/wx_token/jsapi_ticket.json';
        $data = json_decode(file_get_contents($filePath), true);
        if ($data['expire_time'] < time()) {
            $param = http_build_query([
                'type' => 'jsapi',
                'access_token' => $this->getAccessToken(),
            ]);

            // 如果是企业号用以下 URL 获取 ticket
            // $param = http_build_query([
            //     'access_token' => $this->getAccessToken(),
            // ]);
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?" . $param;
            $url = self::TICKET_URL . '?' . $param;
            $res = json_decode(HttpCurl::get($url), true);
            $ticket = $res['ticket'];
            if ($ticket) {
                $data['expire_time'] = time() + 7000;
                $data['jsapi_ticket'] = $ticket;
                file_put_contents($filePath, json_encode($data));
            }
        } else {
            $ticket = $data['jsapi_ticket'];
        }

        return $ticket;
    }

    /**
     * 获取access_token
     */
    public function getAccessToken() {
    	$accessToken = '';
    	$config = $this->_wxConfig;
        // access_token 应该全局存储与更新(如文件、数据库或者redis中)
        $filePath = CORE_PATH . '/extend/wechat/wx_token/access_token.json';
        $data = json_decode(file_get_contents($filePath), true);
        if ($data['expire_time'] < time()) {
            $param = http_build_query([
                'grant_type' => 'client_credential',
                'appid'      => $config['appId'],
                'secret'     => $config['appSecret'],
            ]);

            // 如果是企业号用以下URL获取access_token
            // $param = http_build_query([
            //     'corpid'     => $config['appId'],
            //     'corpsecret' => $config['appSecret'],
            // ]);
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?" . $param;
            $url = self::TOKEN_URL . '?' . $param;
            $res = json_decode(HttpCurl::get($url), true);
            $accessToken = $res['access_token'];
            if ($accessToken) {
                $data['expire_time'] = time() + 7000;
                $data['access_token'] = $accessToken;
                file_put_contents($filePath, json_encode($data));
            }
        } else {
            $accessToken = $data['access_token'];
        }
        return $accessToken;
    }

    /**
     * 发现金红包时，需要该函数
     */
    public function curlSslPost($url = '', $postData = '', $apiclientCert, $apiclientKey, $options = []) {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //第一种方法，cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, $apiclientCert);
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, $apiclientKey);
        //第二种方式，两个文件合成一个.pem文件
        //curl_setopt($ch, CURLOPT_SSLCERT, getcwd().'/all.pem');

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }


    /**
     * 将图片变为圆形
     */
    public function cropToCircle($imgpath, $saveName = '') {
        $src_img = imagecreatefromstring(file_get_contents($imgpath));
        $w = imagesx($src_img);
        $h = imagesy($src_img);
        $w = $h = min($w, $h);
     
        $img = imagecreatetruecolor($w, $h);
        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r = $w / 2; //圆半径
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        
        //返回资源 
        if(!$saveName) return $img;
        //输出图片到文件
        imagepng ($img,$saveName);
        //释放空间
        imagedestroy($src_img);
        imagedestroy($img);
    }

}

