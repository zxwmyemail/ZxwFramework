<?php
namespace core\system;
/********************************************************************************************
 * 应用驱动类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/
use core\system\Route;
use core\system\Config;
use Illuminate\Database\Capsule\Manager;

final class Application {

    public static  $_reqParams;      //请求参数
    public static  $_routeParams;    //路由参数

    /*---------------------------------------------------------------------------------------
    | 启动应用
    |---------------------------------------------------------------------------------------*/
    public static function bootstrap() {

        //初始化系统错误是否显示
        self::isDisplayErrors();

        //防止sql注入检查
        self::checkReqParams();

        //注册数据库连接
        self::registerDbConn();

        //获取请求参数
        $params = Route::handleParams(); 
        self::$_reqParams = $params['request'];
        self::$_routeParams = $params['route'];

        //导向控制层
        self::routeToCtrl();
    }

    /*-------------------------------------------------------------------------------------
    | 注册数据库连接
    --------------------------------------------------------------------------------------*/
    public static function registerDbConn() {
        $dbConfig = Config::get('database');

        $capsule = new Manager();
        // 循环创建所有链接
        foreach ($dbConfig as $name => $config) {
            $capsule->addConnection($config, $name);
        }

        // 设置全局静态可访问DB
        $capsule->setAsGlobal();

        // 启动Eloquent(如果只使用查询构造器，这个可以注释）
        $capsule->bootEloquent();  
    }

    /*-------------------------------------------------------------------------------------
    | 根据目前处于开发、测试还是生产模式，判断是否显示错误到页面
    --------------------------------------------------------------------------------------*/
    public static function isDisplayErrors() {
        error_reporting(E_ALL);
        switch (CUR_ENV) {
            case 'development':
                ini_set('display_errors', 1);
                break;
            case 'test':
                ini_set('display_errors', 1);
                break;
            case 'product':
                ini_set('display_errors', 0);
                break;
            default:
                exit('The application environment is not set correctly.');
                break;
        }
        date_default_timezone_set('Asia/Shanghai');
        ini_set('log_errors', 1); 
        ini_set('error_log', RUNTIME_PATH . DS . 'sys_log' . DS . date('Y-m-d').'.log');
    }

    /*---------------------------------------------------------------------------------------
    | 根据URL分发到Controller
    |---------------------------------------------------------------------------------------*/
    public static function routeToCtrl() {   
        $route = self::$_routeParams;

        $module = $route['module'];
        $ctrl   = ucfirst($route['controller']);
        $action = $route['action'];

        try {
            if (!preg_match('/^[A-Za-z](\w|\.)*$/', $ctrl)) {
                throw new \Exception('controller not exists:' . $ctrl, 404);
            }
            $controller = "app\\" . $module . "\controllers\\" . $ctrl;
            $controllerObj = new $controller;
            $controllerObj->$action(self::$_reqParams);
        } catch (\Throwable $e) {
            error_log('PHP Error:  ' . $e->getMessage() . ' in ' . $e->getFile(). ' on line ' . $e->getLine());
            header('Location: ?r=home.page500');
        }
        
        exit(0);
    }
    
    /*---------------------------------------------------------------------------------------
    | 为防止sql注入和xss攻击，对提交参数进行检查
    ---------------------------------------------------------------------------------------*/
    public static function checkReqParams() {
        $magicQuotesGpc = get_magic_quotes_gpc(); 

        self::daddslashes($_COOKIE); 
        self::daddslashes($_POST); 
        self::daddslashes($_GET); 
        self::daddslashes($_REQUEST); 

        if(!$magicQuotesGpc) { 
            $_FILES = self::daddslashes($_FILES); 
        }
    }

    /*---------------------------------------------------------------------------------------
    | 防止sql注入和xss攻击
    ---------------------------------------------------------------------------------------*/
    public static function daddslashes($data, $ignoreMagicQuotes = true) {
        if(is_string($data)) {   //防止被挂马，跨站攻击
            $data = self::cleanXss($data, true);      
            if(($ignoreMagicQuotes == true) || (!get_magic_quotes_gpc())) {  //防止sql注入
                $data = addslashes($data);            
            }
        } else if(is_array($data)) {
            foreach($data as $key => $value) {
                $data[$key] = self::daddslashes($value, $ignoreMagicQuotes);
            }
        }
        return $data;
    }

    /*---------------------------------------------------------------------------------------
    | 防止xss攻击
    |----------------------------------------------------------------------------------------
    | @param $string
    | @param $low 安全别级低
    ----------------------------------------------------------------------------------------*/
    public static function cleanXss(&$string, $low = false) {
        if (is_array ($string)) {
            foreach ($string as $value) {
                self::cleanXss($value);
            }   
        } else {
            $string = trim($string);
            $string = strip_tags($string);
            $string = htmlspecialchars($string);

            if ($low) return $string;

            $string = str_replace(array('"', "'", "..", "../", "./"), '', $string);
            $no = '/%0[0-8bcef]/';
            $string = preg_replace($no, '', $string);
            $no = '/%1[0-9a-f]/';
            $string = preg_replace($no, '', $string);
            $no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
            $string = preg_replace($no, '', $string);
            return $string;
        }
    }
}

