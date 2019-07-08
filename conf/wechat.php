<?php
/********************************************************************************************
 * 微信支付、登录授权等配置文件
 * @copyright   Copyright(c) 2019
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

return [
    'default' => [
        // 微信公众号appId 
        'appId'         => '', 
        // 微信公众号appSecret         
        'appSecret'     => '',   
        // 微信支付商户号         
        'mchid'         => '',   
        // https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥        
        'apiKey'        => '', 
        // 支付证书cert.pem位置，https://pay.weixin.qq.com 账户中心->账户设置->API安全->下载证书。       
        'apiclientCert' => CORE_PATH . '/extend/wechat/wx_cert/xxxxxx.cert.pem',      
        // 支付证书key.pem位置，https://pay.weixin.qq.com 账户中心->账户设置->API安全->下载证书。     
        'apiclientKey'  => CORE_PATH . '/extend/wechat/wx_cert/xxxxxx.key.pem',
        // 支付成功后，微信通知后端服务器的地址，不能带?号和参数，notify.php放在web根目录下的wechat文件夹    
        'notifyUrl'     => 'https://www.xxx.com/wechat/notify.php',
        // h5支付时，付款成功后，页面跳转的地址        
        'h5ReturnUrl'   => '',
        // h5支付时，WAP网站URL地址       
        'h5WapUrl'      => '',
        // h5支付时，WAP网站名          
        'h5WapName'     => 'H5支付',             
    ],
];

