<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************
 * 核心控制器父类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

class Controller {

    private $_redisConfig  = null;
    private $_memcacheConfig  = null;
    private $_smarty = null;
    private $_jsonRPCClient = null;
        
    public function __construct() 
    {
        $this->setRedisConfig();
        $this->setMemcacheConfig();
    }
    
    /*---------------------------------------------------------------------------------------
    | 获取缓存实例
    |----------------------------------------------------------------------------------------
    | @access  final   public
    | @param   string  $Param    缓存参数，控制层如何获取缓存：
    |                            1、如果获取session实例：
    |                               $session = $this->session;
    |                            2、如果获取redis实例：
    |                               $redis = $this->redis_master;
    |                            3、如果获取memcache实例：
    |                               $memcache = $this->memcache;
    ----------------------------------------------------------------------------------------*/
    public function __get($Param){

        $param = empty($Param) ? 'session' : $Param;
        
        $param = explode('_', $param);

        $name = $param[0];

        $whichCache = empty($param[1]) ? 'master' : $param[1];

        switch ($name) {
            case 'session':
                $cache = new CacheFactory();
                return  $cache->session;
                break;
            case 'redis':
                $cache = new CacheFactory($this->_redisConfig, $whichCache);
                return  $cache->redis;
                break;
            case 'hashRedis':
                $cache = new CacheFactory($this->_redisConfig);
                return  $cache->hashRedis;
                break;
            case 'memcache':
                $cache = new CacheFactory($this->_memcacheConfig);
                return  $cache->memcache;
                break;
            case 'smarty':
                if (empty($this->_smarty)) {
                    $this->setSmarty(); 
                }
                return $this->_smarty;
                break;
            case 'jsonRPCClient':
            	if (empty($this->_jsonRPCClient)) {
                    $this->_jsonRPCClient = Application::newObject('jsonRPCClient', 'jsonRPC');
                }
                return $this->_jsonRPCClient;
            
            default:
                echo '参数错误';exit();
                break;
        }

    }
    
    /*---------------------------------------------------------------------------------------
    | 设置客户端无缓存
    ----------------------------------------------------------------------------------------*/
    public function setNoCache()
    { 
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0",false);
	header("Pragma: no-cache");
    }

    /*-------------------------------------------------------------------------------------------------------------------------
     | 客户端缓存控制函数 
     |-------------------------------------------------------------------------------------------------------------------------
     | $type  缓存类型 
     | $interval  客户端缓存过期时间 
     | $mktime  设置Last-Modified 
     | $etag  设置ETag标志 
     -------------------------------------------------------------------------------------------------------------------------*/    
    public function  http_cache_control( $type = 'nocache' , $interval =0, $mktime = '' , $etag = '' ){       
       if ( $type == 'nocache' )  
       {       
           header('Expires: -1' );  //设置 -1为立刻过期      
           header('Pragma: no-cache' );       
           header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0' );       
       }  
       else   
       {   //检查  ETag: 值   $_SERVER [ 'HTTP_IF_NONE_MATCH' ]  
           if (isset( $_SERVER [ 'HTTP_IF_NONE_MATCH' ]) &&  $etag  &&  $_SERVER [ 'HTTP_IF_NONE_MATCH' ] ==  $etag )  
           {       
                header('HTTP/1.1 304 Not Modfied' );       
           }//检查  Last-Modified: 值   $_SERVER [ 'HTTP_IF_MODIFIED_SINCE' ]  
           elseif (isset( $_SERVER [ 'HTTP_IF_MODIFIED_SINCE' ]) &&  $mktime  &&  $_SERVER [ 'HTTP_IF_MODIFIED_SINCE' ] ==  gmdate ( 'r' , $mktime ). ' GMT' )  
           {       
               header('HTTP/1.1 304 Not Modfied' );       
           }  
           else   
           {     //根据修改时间加过期时间，算出过期时间点  
                if ( $mktime )  
                {       
                   $gmtime  =  gmdate ( 'r' , $mktime + $interval ). ' GMT' ;       
                   header('Expires: ' . $gmtime );       
                }       
                if ( $type == 'public' )//设置缓存类型为public  
                {       
                   header('Cache-Control: public,max-age=' . $interval );       
                }  
                elseif ( $type == 'private' )//设置缓存类型为 private  
                {       
                   header('Cache-Control: private,max-age=' . $interval . ',s-maxage=0' );       
                }elseif ( $type == 'none' )  
                {       
                   header('Cache-Control: must-revalidate,proxy-revalidate' );       
                }       
            }       
            $mktime && header( 'Last-Modified: ' . gmdate ( 'r' , $mktime ) . ' GMT' );       
            $etag   && header( 'ETag: ' . $etag );       
       }       
    }   
    
    /*---------------------------------------------------------------------------------------
    | 设置smarty的路径配置参数
    |----------------------------------------------------------------------------------------
    | @access      public
    | @param       array   $_reqParams
    ----------------------------------------------------------------------------------------*/
    private function setSmarty()
    { 
        $reqParams = Application::$_reqParams;

        $routeConfig = $this->config('route');

        $smarty = new Smarty; 

        $this->_smarty = $smarty;

        $default_module = $routeConfig['default_module'];

        $default_controller = $routeConfig['default_controller'];

        $module = isset($reqParams['module']) ? $reqParams['module'].'Module' : $default_module.'Module';
        
        $controller = isset($reqParams['controller']) ? $reqParams['controller'] : $default_controller;
        
        //设置各个目录的路径，这里是配置smarty的路径参数
        $smarty->template_dir    = VIEW_PATH.'/'.$module.'/'.$controller;
        $smarty->compile_dir     = SYS_FRAMEWORK_PATH."/smarty/templates_c";
        $smarty->config_dir      = SYS_FRAMEWORK_PATH."/smarty/config";
        $smarty->cache_dir       = SYS_FRAMEWORK_PATH."/smarty/cache";
        $smarty->left_delimiter  = "<{";
        $smarty->right_delimiter = "}>";

        //smarty模板有高速缓存的功能，如果这里是true的话即打开caching
        //但是会造成网页不立即更新的问题，当然也可以通过其他的办法解决
        $smarty->caching = false; 
    }
    
    /*-------------------------------------------------------------------------------------
    | 重定向
    |--------------------------------------------------------------------------------------
    | @param  $action     string   重定向路由，格式有两种：
    |                              1. 'controller/action',重定向到其他控制层
    |                              2. 'action',重定向到自身控制层的其他action
    | @param  $param      array    重定向参数
    | @param  $end        bool     重定向后是否终止应用
    | @param  $statusCode int      重定向http请求状态码值，默认302，即重定向
    --------------------------------------------------------------------------------------*/
    public function redirect($action, $param = array(), $end = true, $statusCode = 302){
        
        if (empty($action)) {
            return false;
        }
	
	$url = '';
        $action = explode('/', $action);
        $reqParams = Application::$_reqParams;
        $defaultRoute = $this->config('route');

        if ($defaultRoute['url_type'] == 1) {
            $url = empty($reqParams['module']) ? 'index.php?' : 'index.php?m='.$reqParams['module'].'&';
            if (count($action) == 2) {
                $url .= 'c='.$action[0].'&a='.$action[1];
            } elseif (count($action) == 1) {
                $url .= empty($reqParams['controller']) ? 'c='.$defaultRoute['default_controller'].'&' : 'c='.$reqParams['controller'].'&';
                $url .= 'a='.$action[0];
            } else {
                trigger_error('重定向失败，路由参数【 '.$action.' 】解析失败！');die();
            }

            if (!empty($param)) {
                $url .= "&".http_build_query($param);
            }
        } elseif ($defaultRoute['url_type'] == 2) {
            $url = empty($reqParams['module']) ? 'index.php/' : 'index.php/'.$reqParams['module'].'/';
            if (count($action) == 2) {
                $url .= $action[0].'/'.$action[1];
            } elseif (count($action) == 1) {
                $url .= empty($reqParams['controller']) ? $defaultRoute['default_controller'].'/' : $reqParams['controller'].'/';
                $url .= $action[0];
            } else {
                trigger_error('重定向失败，路由参数【 '.$action.' 】解析失败！');die();
            }

            if (!empty($param)) {
                $url .= '/?'.http_build_query($param);
            }
        }

        header("Location:".$url, true, $statusCode);

        if ($end) {
            exit();
        }
    }

    /*--------------------------------------------------------------------------------------
    | 加载系统配置,默认为系统配置 $CONFIG['system'][$config]
    |---------------------------------------------------------------------------------------
    | @access      final   protected
    | @param       string  $config 配置名  
    --------------------------------------------------------------------------------------*/
    protected function config($config){
        return Application::$_config[$config];
    }

    /*--------------------------------------------------------------------------------------
    | 加载redis参数配置
    --------------------------------------------------------------------------------------*/
    protected function setRedisConfig(){
        $this->_redisConfig = Application::$_config['redis'][CUR_ENV];
    }

    /*--------------------------------------------------------------------------------------
    | 加载memcache参数配置
    --------------------------------------------------------------------------------------*/
    protected function setMemcacheConfig(){
        $this->_memcacheConfig = Application::$_config['memcache'][CUR_ENV];
    }


}


