<?php
namespace core\extend\monolog;
/********************************************************************************************
 日志记录类，github地址： 
 https://github.com/Seldaek/monolog
*********************************************************************************************/
use core\system\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\JsonFormatter;

class Log {
    
    private $logger;
    private $streamHandler;

    // 单例模式，保存本类实例   
    private static $_instance = [];      

    /**
     * 私有化克隆机制
     */
    private function __clone() {}
  
    /**
     * 公共静态方法获取实例化的对象
     */
    public static function getInstance($module = '') {  
        $routeConf = Config::get('config', 'route');
        $module = $module ? $module : $routeConf['default_module'];
        if(!isset(self::$_instance[$module])){  
            self::$_instance[$module] = new self($module);   
        }
        return self::$_instance[$module];  
    }  
    
    /**
     * 构造函数
     *
     * @param string  $module       标识是哪个模块的日志
     * @param string  $formatter    日志格式：json、line
     * @param string  $filterLevel  过滤级别
     *
     */
    private function __construct($module) {
        $logFile = RUNTIME_PATH.'/app_log/' . $module . '/' . date('Y') . '/'. date('Y-m-d') . '.log';
        $this->logger = new Logger($module);
        $this->streamHandler = new StreamHandler($logFile, Logger::DEBUG);
    }

    /**
     * 处理日志格式
     * @param string  $msg     日志信息
     * @param array   $context 日志上下文
     */
    private function handleFormat($context, $formatter) {
        switch (strtolower($formatter)) {
            case "line":
                // 默认格式为： "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
                $dateFormat = "Y-m-d H:i:s";
                $output = "[%datetime%] %channel%.%level_name%: %message%\n";
                if ($context) {
                    $output = "[%datetime%] %channel%.%level_name%: %message% %context%\n";
                }
                $formatter = new LineFormatter($output, $dateFormat);
                $this->streamHandler->setFormatter($formatter);
                break;
            case "json":
                $this->streamHandler->setFormatter(new JsonFormatter());
                break;
            default:
        }
        $this->logger->pushHandler($this->streamHandler);
    }

    /**
     * 严重错误日志
     * @param string  $msg     日志信息
     * @param array   $context 日志上下文
     */
    public function critical($msg, $context = [], $formatter = 'line') {
        $this->handleFormat($context, $formatter);
        $this->logger->critical($msg, $context);
    }

    /**
     * 错误日志
     * @param string  $msg     日志信息
     * @param array   $context 日志上下文
     */
    public function error($msg, $context = [], $formatter = 'line') {
        $this->handleFormat($context, $formatter);
        $this->logger->error($msg, $context);
    }

    /**
     * 警告日志
     * @param string  $msg     日志信息
     * @param array   $context 日志上下文
     */
    public function warning($msg, $context = [], $formatter = 'line'){
        $this->handleFormat($context, $formatter);
        $this->logger->warning($msg, $context);
    }

    /**
     * 通知日志
     * @param string  $msg     日志信息
     * @param array   $context 日志上下文
     */
    public function notice($msg, $context = [], $formatter = 'line'){
        $this->handleFormat($context, $formatter);
        $this->logger->notice($msg, $context);
    }

    /**
     * 信息日志
     * @param string  $msg     日志信息
     * @param array   $context 日志上下文
     */
    public function info($msg, $context = [], $formatter = 'line'){
        $this->handleFormat($context, $formatter);
        $this->logger->info($msg, $context);
    }

    /**
     * 调试日志
     * @param string  $msg     日志信息
     * @param array   $context 日志上下文
     */
    public function debug($msg, $context = [], $formatter = 'line'){
        $this->handleFormat($context, $formatter);
        $this->logger->debug($msg, $context);
    }

}

?>
