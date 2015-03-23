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

    protected $_redisConfig  = null;
    protected $_memcacheConfig  = null;
        
    public function __construct() 
    {
        $this->setRedisConfig();
        $this->setMemcacheConfig();
    }


   /*---------------------------------------------------------------------------------------
    | 获取缓存实例
    | @access  final   public
    | @param   string  $Param    缓存参数，控制层如何获取缓存：
    |                            1、如果获取session实例：
    |                               $session = $this->session;
    |                            2、如果获取redis实例：
    |                               $redis = $this->redis;
    |                            3、如果获取memcache实例：
    |                               $memcache = $this->memcache;
    --------------------------------------------------------------------------------------*/
    final public function __get($Param){

        $param = empty($Param) ? 'session' : $Param;

        switch (strtolower($param)) {
            case 'session':
                $cache = new CacheFactory();
                return  $cache->session;
                break;
            case 'redis':
                $cache = new CacheFactory($this->_redisConfig);
                return  $cache->redis;
                break;
            case 'memcache':
                $cache = new CacheFactory($this->_memcacheConfig);
                return  $cache->memcache;
                break;
            
            default:
                echo '参数错误';exit();
                break;
        }

    }


   /*---------------------------------------------------------------------------------------
    | 加载系统配置,默认为系统配置 $CONFIG['system'][$config]
    | @access      final   protected
    | @param       string  $config 配置名  
    --------------------------------------------------------------------------------------*/
    final protected function config($config){
        return Application::$_config[$config];
    }


   /*---------------------------------------------------------------------------------------
    | 加载redis参数配置
    --------------------------------------------------------------------------------------*/
    final protected function setRedisConfig(){
        $this->_redisConfig = Application::$_config['redis'];
    }


   /*---------------------------------------------------------------------------------------
    | 加载memcache参数配置
    --------------------------------------------------------------------------------------*/
    final protected function setMemcacheConfig(){
        $this->_memcacheConfig = Application::$_config['memcache'];
    }

    
}


