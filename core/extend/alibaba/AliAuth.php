<?php
namespace core\extend\alibaba;
/********************************************************************************************
 阿里登录授权、获取用户信息、获取分享参数，使用方法：
 
 // 第一步：写一个接口，里面放上如下代码来获取auth_code
 $redirectUrl = '填写回调接口地址';
 $auth = new AliAuth();
 $auth->getAuthCode($redirectUrl); 

 // 第二步：再写一个接口，在阿里回调的redirectUrl对应的接口方法中获取用户信息
 $auth = new AliAuth();
 $user = $auth->getUserInfo(); 
 var_dump($user);
*********************************************************************************************/
use core\library\HttpCurl;
use core\extend\monolog\Log;

class AliAuth extends Alibaba {

    private $scope = 'auth_base';

    /**
     * 构造函数
     * @param string $whichConf 配置文件中哪一组配置
     * @param string $scope auth_base或auth_userinfo。
     *                      如果只需要获取用户id，填写auth_base即可；
     *                      如需获取头像、昵称等信息，填写auth_userinfo；
     */
    public function __construct($scope = 'auth_user', $whichConf = 'default') {
        parent::__construct($whichConf);
        $this->scope = $scope;
    }

    /**
     * 获取授权需要的auth_code参数
     * @param string $redirectUrl 阿里回调url，即附加auth_code参数的url地址
     */
    public function getAuthCode($redirectUrl)
    {
        //触发微信返回code码
        $url = $this->createOauthUrlForCode($redirectUrl);
        Header("Location: $url");
        exit();
    }

    /**
     * 获取用户信息
     * @return array 用户信息
     */
    public function getUserInfo()
    {
        $authCode = $_GET['auth_code'];
        $tokenInfo = $this->getTokenInfoFromAli($authCode);
        
        $log = Log::getInstance(); 
        if(!isset($tokenInfo['alipay_system_oauth_token_response'])) {
            $errorMsg = is_array($tokenInfo) ? json_encode($tokenInfo) : $tokenInfo;
            $log->error('获取阿里token信息失败：' . $errorMsg);
            return false;
        }

        $baseInfo = $tokenInfo['alipay_system_oauth_token_response'];
        if($this->scope == 'auth_base') {
            $user['user_id'] = $baseInfo['user_id'];
            return $user;
        }
        
        $userinfo = $this->getUserInfoFromAli($baseInfo['access_token']);
        if($userinfo['alipay_user_userinfo_share_response']) {
            return $userinfo['alipay_user_userinfo_share_response'];
        } else {
            $errorMsg = is_array($userinfo) ? json_encode($userinfo) : $userinfo;
            $log->error('获取阿里用户信息失败：' . $errorMsg);
            return false;
        }
    }

    /**
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     * @return 返回构造好的url
     */
    private function createOauthUrlForCode($redirectUrl)
    {
        $config = $this->_aliConf;
        $urlObj["app_id"]       = $config['appId'];
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["scope"]        = $this->scope;
        $bizString              = $this->ToUrlParams($urlObj);
        return self::AUTHORIZE_URL . '?' . $bizString;
    }

    /**
     * 从阿里获取token信息
     * @return array
     */
    public function getTokenInfoFromAli($authCode)
    {
        $config = $this->_aliConf;
        $commParams = array(
            'app_id'     => $config['appId'],
            'method'     => 'alipay.system.oauth.token',//接口名称
            'format'     => 'JSON',
            'charset'    => $config['charset'],
            'sign_type'  => $config['signType'],
            'timestamp'  => date('Y-m-d H:i:s'),
            'version'    => $config['version'],
            'grant_type' => 'authorization_code',
            'code'       => $authCode,
        );
        $commParams["sign"] = $this->getSign($commParams);
        $result = HttpCurl::post(self::ALIPAY_GATEWAY_URL, $reqParams);
        $result = iconv('GBK','UTF-8',$result);
        return json_decode($result, true);
    }


    /**
     * 从阿里获取用户信息
     * @return array
     */
    public function getUserInfoFromAli($token)
    {
        $config = $this->_aliConf;
        $commParams = [
            'app_id'     => $config['appId'],
            'method'     => 'alipay.user.userinfo.share', //接口名称
            'format'     => 'JSON',
            'charset'    => $config['charset'],
            'sign_type'  => $config['signType'],
            'timestamp'  => date('Y-m-d H:i:s'),
            'version'    => $config['version'],
            'auth_token' => $token,
        ];
        $commParams["sign"] = $this->getSign($commParams);
        $result = HttpCurl::post(self::ALIPAY_GATEWAY_URL, $reqParams);
        $result = iconv('GBK','UTF-8',$result);
        return json_decode($result, true);
    }

}
