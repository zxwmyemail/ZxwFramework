<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************
 * 应用驱动类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

final class Application {

    public static  $_config = null;         //系统配置参数，对应params.config.php文件
    public static  $_reqParams = null;      //请求url参数

   /*---------------------------------------------------------------------------------------
    | 创建应用
    | @access      public
    | @param       array   $config
    ---------------------------------------------------------------------------------------*/
    public static function run($config)
    {
        //自动类加载函数
        self::registerAutoload(); 

        //初始化系统错误是否显示
        self::isDisplayErrors();

        //防止sql注入检查
        self::checkRequestParams();

        //加载config/下的参数配置params.config.php
        self::$_config = $config['system'];

        //获取路由对象
        $route = new Route(self::$_config['route']['url_type']);

        //将url参数转换成数组 
        self::$_reqParams = $route->getUrlArray();   

        //导向控制层
        self::routeToCtrl(self::$_reqParams);
        
    }
    
    /*------------------------------------------------------------------------------------------
    | 注册自动加载类函数
    --------------------------------------------------------------------------------------------*/
    public static function registerAutoload($enable = true)
    {
        $enable ? spl_autoload_register('self::classLoader') : spl_autoload_unregister('self::classLoader');
    }

    /*-------------------------------------------------------------------------------------
    | 记录系统日志，判断是否显示日志
    --------------------------------------------------------------------------------------*/
    public static function isDisplayErrors()
    {
        error_reporting(E_ALL);
        switch (CUR_ENV) {
            case 'development':
                ini_set('display_errors',1);
                break;
            case 'test':
            case 'product':
                ini_set('display_errors',0);
                break;
            default:
                exit('The application environment is not set correctly.');
                break;
        }

        ini_set('log_errors',1); 
        ini_set('error_log',LOG_PATH.'/sys_log/'.date('Ymd').'.txt');
    }

   /*---------------------------------------------------------------------------------------
    | 手动进行类加载的函数
    | @access    public
    | @param     string   $classname  类名
    | @param     string   $pathKey    在param.config.php文件中注册的类所在路径的键
    | @param     int      $model      模式: 'new'表示返回类，
    |                                       'static'表示只加载文件，不返回类
    | @return    找不到类，返回false,否则依据model的值返回相应的类
    --------------------------------------------------------------------------------------*/
    public static function newClass($classname, $pathKey, $model='new')
    {
        $filename = $classname.'.php';

        $dir = self::$_config['newClassPath'][$pathKey];
        
        $classFile = $dir.'/'.$classname.'.php';

        if (file_exists($classFile)) {
            return false;
        }else{
            require_once($classFile);
            if ($model=='new') {
                return new $classname;
            }
        }
    }

   /*---------------------------------------------------------------------------------------
    | 自动类加载函数
    | @access      public
    | @param       string   $classname  类名
    ---------------------------------------------------------------------------------------*/
    public static function classLoader($classname)     
    {     
        $mvc_model = MODEL_PATH.'/'.$classname.".php"; 

        $sys_lib = SYS_LIB_PATH.'/'.$classname.'.php';

        $sys_core = SYS_CORE_PATH.'/'.$classname.'.php';

        if (file_exists($mvc_model)){     
            require_once($mvc_model);     
        } elseif (file_exists($sys_lib)){     
            require_once($sys_lib);     
        } elseif (file_exists($sys_core)){     
            require_once($sys_core);     
        } else {
            trigger_error('加载 '.$classname.' 类库不存在');
        }
    }

   /*---------------------------------------------------------------------------------------
    | 根据URL分发到Controller
    | @access      public 
    | @param       array   $url_array     
    ---------------------------------------------------------------------------------------*/
    public static function routeToCtrl($url_array = array())
    {   
        $module = empty($url_array['module']) ? self::$_config['route']['default_module'].'Module' : $url_array['module'].'Module';
        
        $controller = empty($url_array['controller']) ? self::$_config['route']['default_controller'].'Controller' : $url_array['controller'].'Controller';
        
        $controller_file = CONTROLLER_PATH.'/'.$module.'/'.$controller.'.php';

        $action = empty($url_array['action']) ? self::$_config['route']['default_action'] : $url_array['action'];

        $params = empty($url_array['params']) ? '' : $url_array['params'];
        
        if(file_exists($controller_file)){
            require $controller_file;
            $controller = new $controller;
        }else{
            die('控制器不存在');
        }

        if($action){
            if(method_exists($controller, $action))
                isset($params) ? $controller ->$action($params) : $controller ->$action();
            else
                die('控制器方法不存在');
        }else{
            die('控制器方法不存在');
        }

    }
    
    /*---------------------------------------------------------------------------------------
    | 为防止sql注入和xss攻击，对提交参数进行检查
    ---------------------------------------------------------------------------------------*/
    public static function checkRequestParams() 
    {
        $magic_quotes_gpc = get_magic_quotes_gpc(); 

        self::daddslashes($_COOKIE); 
        self::daddslashes($_POST); 
        self::daddslashes($_GET); 
        self::daddslashes($_REQUEST); 

        if(!$magic_quotes_gpc) { 
            $_FILES = self::daddslashes($_FILES); 
        }

    }

   /*---------------------------------------------------------------------------------------
    | 防止sql注入和xss攻击
    ---------------------------------------------------------------------------------------*/
    public static function daddslashes($data, $ignore_magic_quotes = true)
    {
        if(is_string($data))
        {
            $data = self::cleanXss($data);            //防止被挂马，跨站攻击
            if(($ignore_magic_quotes == true) || (!get_magic_quotes_gpc())) 
            {
                $data = addslashes($data);            //防止sql注入
            }
        }else if(is_array($data)){
            foreach($data as $key => $value)
            {
                $data[$key] = self::daddslashes($value, $ignore_magic_quotes);
            }
        }
        
        return $data;
        
    }

   /*---------------------------------------------------------------------------------------
    | 防止xss攻击
    | @param $string
    | @param $low 安全别级低
    ---------------------------------------------------------------------------------------*/
    public static function cleanXss(&$string, $low = False)
    {
        if (is_array ( $string )){
            foreach ($string as $value) {
                self::cleanXss( $value );
            }   
        } else {
            $string = trim ( $string );
            $string = strip_tags ( $string );
            $string = htmlspecialchars ( $string );
            if ($low)
            {
                return $string;
            }
            $string = str_replace ( array ('"', "\\", "'", "/", "..", "../", "./", "//" ), '', $string );
            $no = '/%0[0-8bcef]/';
            $string = preg_replace ( $no, '', $string );
            $no = '/%1[0-9a-f]/';
            $string = preg_replace ( $no, '', $string );
            $no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
            $string = preg_replace ( $no, '', $string );
            return $string;
        }
    }


}

