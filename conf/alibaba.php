<?php
/********************************************************************************************
 * 阿里巴巴支付、登录授权等配置文件
 * @copyright   Copyright(c) 2019
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

return [
    'default' => [
        // https://open.alipay.com 账户中心->密钥管理->开放平台密钥，填写添加了电脑网站支付的应用的APPID 
        'appId'           => '', 
        // 阿里巴巴公众号appSecret         
        'appSecret'       => '', 
        // 签名算法类型，支持RSA2和RSA，推荐使用RSA2     
        'signType'        => 'RSA2',  
        // 对应签名算法类型的商户私钥，密钥生成参考：https://docs.open.alipay.com/291/105971和https://docs.open.alipay.com/200/105310     
        'rsaPrivateKey'   => '', 
        'alipayPublicKey' => '',
        // 设置字符编码集         
        'charset'         => 'utf8', 
        // 设置接口版本号      
        'version'         => '1.0', 
        // 支付成功后，异步回调地址，notify.php放在web根目录下的alibaba文件夹    
        'notifyUrl'       => 'https://www.xxx.com/alibaba/notify.php', 
        // 支付成功后，同步回调地址，return.php放在web根目录下的alibaba文件夹    
        'returnUrl'       => 'https://www.xxx.com/alibaba/return.php',                
    ],
];

