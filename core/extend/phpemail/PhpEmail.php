<?php
namespace core\extend\phpemail;
/********************************************************************************************
 邮件类，github地址： 
 https://github.com/PHPMailer/PHPMailer/
 PHPMailer 需要PHP的sockets和openssl的扩展支持；
*********************************************************************************************/
use core\system\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PhpEmail {

    private $mailIns; 

    // 单例模式，保存本类实例   
    private static $_instance = [];      

    /**
     * 私有化克隆机制
     */
    private function __clone() {}
  
    /**
     * 公共静态方法获取实例化的对象
     */
    public static function getInstance($whichEmail = 'default') {  
        if(!isset(self::$_instance[$whichEmail])){  
            self::$_instance[$whichEmail] = new self($whichEmail);   
        }
        return self::$_instance[$whichEmail];  
    }  

    public function __construct($whichEmail = 'default') {
        $config = Config::get('email', $whichEmail);

        $mail = new PHPMailer(true);
        //Server settings
        $mail->SMTPDebug  = $config['smtp_debug']; 
        $mail->isSMTP();                                            
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = $config['smtp_auth']; 
        $mail->Username   = $config['username']; 
        $mail->Password   = $config['password']; 
        $mail->SMTPSecure = $config['smtp_secure']; 
        $mail->Port       = $config['port'];

        // 设置邮件发送人
        $mail->setFrom($config['username']); 

        $this->mailIns = $mail;    
    }

    /**
     * 发送邮件
     *
     * @param string  $subject  邮件标题
     * @param string  $body     邮件内容
     * @param string  $from     发送人地址，例如：xxx@163.com
     * @param array   $to       接收人地址，如：['xxx@163.com', 'xxx@163.com', 'xxx@163.com']
     * @param boolean $isHTML   邮件内容是否为html格式
     * @param array   $options  其它选项，如：
                                [
                                    'replyTo' => ['xxx@163.com', 'xxx@163.com'],  // 回复人地址
                                    'ccTo'    => ['xxx@163.com', 'xxx@163.com'],  // 抄送人地址
                                    'bccTo'   => ['xxx@163.com', 'xxx@163.com'],  // 密送人地址
                                    'attachment' => ['/var/a.jpg', '/var/b.doc'], // 附件文件路径
                                ]
     */
    public function send($subject, $body, $to, $isHTML = true, $options = []) {
        try { 
            $mail = $this->mailIns;
            // 设置邮件标题
            $mail->Subject = $subject;

            // 设置邮件内容
            $mail->isHTML($isHTML);
            $mail->Body = $body;
            $mail->AltBody = $body;

            // 设置邮件接收人
            if (is_array($to) && !empty($to)) {
                foreach ($to as $address) {
                    $mail->addAddress($address);
                }
            }

            // 设置回复人
            if (isset($options['replyTo'])) {
                foreach ($options['replyTo'] as $address) {
                    $mail->addReplyTo($address);
                }
            }

            // 设置抄送人
            if (isset($options['ccTo'])) {
                foreach ($options['ccTo'] as $address) {
                    $mail->addCC($address);
                }
            }

            // 设置密送人
            if (isset($options['bccTo'])) {
                foreach ($options['bccTo'] as $address) {
                    $mail->addBCC($address);
                }
            }

            // 添加附件
            if (isset($options['attachment'])) {
                foreach ($options['attachment'] as $path) {
                    $mail->addAttachment($path);
                }
            }
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email message could not be sent. Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
}

