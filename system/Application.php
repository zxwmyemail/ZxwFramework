<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************
 * 应用驱动类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 *******************************************************************************************/

final class Application {

    public static  $_config = null;         //系统配置参数，对应params.config.php文件
    public static  $_smarty = null;         //smarty对象
    public static  $_reqParams = null;      //请求url参数
    public static  $allFiles = array();


   /*---------------------------------------------------------------------------------------
    | 创建应用
    | @access      public
    | @param       array   $config
    ---------------------------------------------------------------------------------------*/
    public static function run($config)
    {
        //自动类加载函数
        spl_autoload_register('self::classLoader');  

        //防止sql注入检查
        self::checkRequestParams();

        //加载config/下的参数配置params.config.php
        self::$_config = $config['system'];

        //设置url的类型
        $route = new Route(self::$_config['route']['url_type']);

        //将url参数转换成数组 
        self::$_reqParams = $route->getUrlArray();   

        //加载smarty模板引擎
        self::setTemplatesPath(self::$_reqParams);

        //导向控制层
        self::routeToCtrl(self::$_reqParams);
        
    }


   /*---------------------------------------------------------------------------------------
    |获取某个文件夹下面的所有文件
    |@param   $dir 文件夹路径
    --------------------------------------------------------------------------------------*/
    public static function scanAllFiles ( $dir, $filename )
    {
        $handle = opendir($dir);
        if ( $handle ){
            while ( ( $file = readdir ( $handle ) ) !== false ){
                if ( $file != '.' && $file != '..'){
                    $cur_path = $dir . DIRECTORY_SEPARATOR . $file;
                    if ( is_dir ( $cur_path ) ){
                        if (empty(self::$allFiles)) {
                            self::scanAllFiles ( $cur_path, $filename  );
                        }
                    }else{
                        if ($filename == $file) {
                            self::$allFiles[] = $cur_path;
                        }
                    }
                }
            }
            closedir($handle);
        }
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
    public static function newClass($classname, $pathKey, $model='new'){

        $filename = $classname.'.php';

        $dir = self::$_config['newClassPath'][$pathKey];

        if (empty($dir)) {
            return false;
        }

        self::$allFiles = array();

        self::scanAllFiles($dir, $filename);

        if (!empty(self::$allFiles)) {
            require_once(self::$allFiles[0]);
            if ($model=='new') {
                return new $classname;
            }
        }else{
            return false;
        }

    }


   /*---------------------------------------------------------------------------------------
    | 自动类加载函数
    | @access      public
    | @param       string   $classname  类名
    ---------------------------------------------------------------------------------------*/
    public static function classLoader($classname)     
    {     
        $defaultController = self::$_config['route']['default_controller'];

        $controller = isset(self::$_reqParams['controller']) ? self::$_reqParams['controller'] : $defaultController;

        $mvc_model = MODEL_PATH.'/'.$controller.'/'.$classname.".php"; 

        $mvc_model_default = MODEL_PATH.'/'.$classname.".php"; 

        $sys_lib = SYS_LIB_PATH.'/'.$classname.'.php';

        $sys_core = SYS_CORE_PATH.'/'.$classname.'.php';

        if (file_exists($mvc_model)){     
            require_once($mvc_model);     
        } elseif (file_exists($mvc_model_default)){     
            require_once($mvc_model_default);     
        } elseif (file_exists($sys_lib)){     
            require_once($sys_lib);     
        } elseif (file_exists($sys_core)){     
            require_once($sys_core);     
        } else {
            trigger_error('加载 '.$classname.' 类库不存在');
        }
    }   


   /*---------------------------------------------------------------------------------------
    | 设置smarty的路径配置参数
    | @access      public
    | @param       array   $_reqParams
    --------------------------------------------------------------------------------------*/
    public static function setTemplatesPath($reqParams)
    {
        require_once SYS_FRAMEWORK_PATH.'/smarty/libs/Smarty.class.php'; 

        $smarty = new Smarty; 

        self::$_smarty = $smarty;

        $default_controller = self::$_config['route']['default_controller'];

        //设置各个目录的路径，这里是配置smarty的路径参数
        $controller = isset($reqParams['controller']) ? $reqParams['controller'] : $default_controller;
        $smarty->template_dir    = VIEW_PATH.'/'.$controller;
        $smarty->compile_dir     = SYS_FRAMEWORK_PATH."/smarty/templates_c";
        $smarty->config_dir      = SYS_FRAMEWORK_PATH."/smarty/config";
        $smarty->cache_dir       = SYS_FRAMEWORK_PATH."/smarty/cache";
        $smarty->left_delimiter  = "<{";
        $smarty->right_delimiter = "}>";

        //smarty模板有高速缓存的功能，如果这里是true的话即打开caching
        //但是会造成网页不立即更新的问题，当然也可以通过其他的办法解决
        $smarty->caching = false; 

    }


   /*---------------------------------------------------------------------------------------
    | 根据URL分发到Controller
    | @access      public 
    | @param       array   $url_array     
    ---------------------------------------------------------------------------------------*/
    public static function routeToCtrl($url_array = array())
    {   
        $controller = empty($url_array['controller']) ? self::$_config['route']['default_controller'].'Controller' : $url_array['controller'].'Controller';
        
        $controller_file = CONTROLLER_PATH.'/'.$controller.'.php';

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

