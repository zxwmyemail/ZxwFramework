<?php
namespace core\extend\captcha;
/********************************************************************************************
 验证码，github地址： 
 https://github.com/Gregwar/Captcha

 <img src="?r=home.verifyCode" onclick="this.src='?r=home.verifyCode&'+Math.random();"></img>
*********************************************************************************************/
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

class Captcha {

    private $length = 4;
    private $width  = 150;
    private $height = 50;
    private $enSet  = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function __construct($length = 4, $width = 150, $height = 50) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $this->length = $length;
        $this->width  = $width;
        $this->height = $height;
    }

    /**
     * 输出验证码图片到页面
     */
    public function output() {
        $phraseBuilder = new PhraseBuilder($this->length, $this->enSet);
        $captcha = new CaptchaBuilder(null, $phraseBuilder);

        // 宽，高，无字体
        $captcha->build($this->width, $this->height, null);

        $userFlag = substr(md5(session_id()), 8, 16) . '_captcha_@user';

        $_SESSION[$userFlag] = $captcha->getPhrase();

        ob_clean();
        header('Content-type: image/jpeg'); 
        $captcha->output();
    }

    /**
     * 比对用户输入的验证码
     */
    public function compare($userInput) {
        $userFlag = substr(md5(session_id()), 8, 16) . '_captcha_@user';
        $originCaptcha = $_SESSION[$userFlag];
        unset($_SESSION[$userFlag]);

        return $originCaptcha == $userInput ? true : false;
    }
    
}

