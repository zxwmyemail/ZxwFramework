<?php
namespace core\extend\wechat;
/********************************************************************************************
 微信登录授权、获取用户信息、获取分享参数
 使用方法：
    // 第一步：写一个接口，里面放上如下代码来获取code
    $redirectUrl = '填写回调接口地址';
    $auth = new WxAuth();
    $auth->getCode($redirectUrl); 

    // 第二步：再写一个接口，在微信回调的redirectUrl对应的接口方法中获取openid
    $auth = new WxAuth();
    $user = $auth->getUserInfo();
    var_dump($user);

    // 获取分享参数，接口代码如下：
    $preUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $url    = isset($_REQUEST['url']) ? $_REQUEST['url'] : $preUrl;
    $auth = new WxAuth();
    $signPackage = $auth->getSignPackage($url);

 部署时注意：
    wx_token文件夹需要赋予读写权限，最好777权限
*********************************************************************************************/
use core\library\HttpCurl;
use core\extend\monolog\Log;

class WxAuth extends Wechat {

    private $scope = 'snsapi_userinfo';

    const ACODE_URL           = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit';
    const AUTHORIZE_URL       = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    const OAUTH2_TOKEN_URL    = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    const OAUTH2_USERINFO_URL = 'https://api.weixin.qq.com/sns/userinfo';

    /**
     * 构造函数
     * @param string $whichConf 配置文件中哪一组配置
     * @param string $scope asnsapi_base或asnsapi_userinfo。
     *                      如果只需要获取用户id，填写asnsapi_base即可；
     *                      如需获取头像、昵称等信息，填写asnsapi_userinfo；
     */
    public function __construct($scope = 'snsapi_userinfo', $whichConf = 'default') {
        parent::__construct($whichConf);
        $this->scope = $scope;
    }

    /**
     * 获取授权需要的code参数
     * @param string $redirectUrl 微信回调url，即附加code参数的url地址
     */
    public function getCode($redirectUrl)
    {
        //触发微信返回code码
        $url = $this->createOauthUrlForCode($redirectUrl);
        Header("Location: $url");
        exit();
    }

    /**
     * 获取用户信息
     * @return 用户的信息
     */
    public function getUserInfo()
    {
        //获取code码，以获取openid
        $code = $_GET['code'];
        $url = $this->createOauthUrlForOpenid($code);        
        $result = json_decode(HttpCurl::get($url), true);

        if(!isset($result['openid'])) {
            $log = Log::getInstance(); 
            $log->error('获取微信用户信息失败：' . json_encode($result));
            return false;
        }

        if ($this->scope == 'snsapi_base') {
            return ['openid' => $result['openid']];
        } 

        $params  = 'access_token=' . $result['access_token'] . '&';
        $params .= 'openid=' . $result['openid'] . '&lang=zh_CN';
        $response = HttpCurl::get(self::OAUTH2_USERINFO_URL . '?' . $params);
        return json_decode($response, true);
    }

    /**
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     * @return 请求的url
     */
    private function createOauthUrlForOpenid($code)
    {
        $config = $this->_wxConfig;
        $urlObj["appid"]      = $config['appId'];
        $urlObj["secret"]     = $config['appSecret'];
        $urlObj["code"]       = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->toUrlParams($urlObj);
        return self::OAUTH2_TOKEN_URL . "?" . $bizString;
    }

    /**
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     * @return 返回构造好的url
     */
    private function createOauthUrlForCode($redirectUrl)
    {
        $config = $this->_wxConfig;
        $urlObj["appid"]         = $config['appId'];
        $urlObj["redirect_uri"]  = urlencode($redirectUrl);
        $urlObj["scope"]         = $this->scope;
        $urlObj["response_type"] = "code";
        $urlObj["state"]         = "STATE";
        $bizString = $this->toUrlParams($urlObj);
        return self::AUTHORIZE_URL . '?' . $bizString;
    }

    /**
     * 获取分享参数
     * @param string $url 
     * @return array
     */
    public function getSignPackage($url = '') {
        $timestamp = time();
        $jsapiTicket = $this->getJsApiTicket();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = $url ? $url : "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $config = $this->_wxConfig;
        return [
            "appId"     => $config['appId'],
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => sha1($string),
            "rawString" => $string
        ];
    }

    /**
     * 获取小程序二维码
     * @param array $scene 需要传递的参数,微信限制(最大32个可见字符,键名+键值+连接符['='|'&']<=32)
     * @param string $page 已经发布的小程序存在的页面，根路径前不要填加'/'，不能携带参数（参数请放在scene字段里）
     * @param number $width 二维码的宽度 
     * @param boolean $auto_color 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array $line_color auto_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示
     * @param boolean $is_hyaline 是否需要透明底色， is_hyaline 为true时，生成透明底色的小程序码
     * @return boolean|mixed
     */
    public function saveWxAcode($scene = '', $width = 430, $isHyaline = false)
    {
        $token = $this->getAccessToken();
        if (!$token) return false;

        $acodeUrl = self::ACODE_URL . '?access_token=' . $token;
        $acodeData = json_encode([
            'scene'      => $scene,
            'page'       => '',
            'width'      => $width,
            'auto_color' => false,
            'line_color' => ['r'=>'0','g'=>'0','b'=>'0'],
            'is_hyaline' => $isHyaline,
        ]);

        $res = HttpCurl::post($acodeUrl, $acodeData);;
        return "data:image/jpeg;base64," . base64_encode($res);
    }

}
