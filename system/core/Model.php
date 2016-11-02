<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/*******************************************************************************************
 * 核心model层父类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 *******************************************************************************************/

class Model {

    protected $_mysqlConfig    = null;
    protected $_oracleConfig   = null;
    protected $_redisConfig    = null;
    protected $_memcacheConfig = null;

    public function __construct() {
        $this->setDbConfig();
        $this->setRedisConfig(); 
        $this->setMemcacheConfig();              
    }

    /*--------------------------------------------------------------------------------------
    | 获取数据库和缓存实例
    |---------------------------------------------------------------------------------------
    | @access  final   public
    | @param   string  $name    数据库名字：mysql 、mysqlPDO 、oracle 、oraclePDO 、redis 、hashRedis 、memcache
    | @param   string  $whichDB  哪一个配置：
    |                            1、对于数据库：
    |                               对于配置文件params.config.php文件中
    |                               如果想获取mysql的master数据库连接实例，
    |                               那么$whichDB = master
    |                            2、对于缓存
    |                               如果获取redis的master实例：
    |                               那么$whichDB = 'master'
    --------------------------------------------------------------------------------------*/
    public function getDB($name, $whichDB = 'master'){
        switch ($name) {
            case 'mysql':
                $DB = new DBFactory($this->_mysqlConfig, $whichDB);
                return  $DB->mysql;
                break;
            case 'mysqlPDO':
                $DB = new DBFactory($this->_mysqlConfig, $whichDB);
                return  $DB->mysqlPDO;
                break;
            case 'oracle':
                $DB = new DBFactory($this->_oracleConfig, $whichDB);
                return  $DB->oracle;
                break;
            case 'oraclePDO':
                $DB = new DBFactory($this->_oracleConfig, $whichDB);
                return  $DB->oraclePDO;
                break;
            case 'session':
                $cache = new CacheFactory();
                return  $cache->session;
                break;
            case 'redis':
                $cache = new CacheFactory($this->_redisConfig, $whichDB);
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
                    
            default:
                echo '参数错误';exit();
                break;
            }

    }

    /*-----------------------------------------------------------------------------------
    | 根据表前缀获取表名
    |------------------------------------------------------------------------------------
    | @access      final   protected
    | @param       string  $table_name    表名
    -----------------------------------------------------------------------------------*/
    protected function table($table_name){
        $config_db = $this->config('db');
        return $config_db['db_table_prefix'].$table_name;
    }

    /*-----------------------------------------------------------------------------------
    | 加载系统配置,默认为系统配置 $CONFIG['system'][$config]
    |------------------------------------------------------------------------------------
    | @access      final   protected
    | @param       string  $config 配置名  
    -----------------------------------------------------------------------------------*/
    protected function config($config){
        return Application::$_config[$config];
    }

    /*-----------------------------------------------------------------------------------
    | 加载数据库DB参数配置
    -----------------------------------------------------------------------------------*/
    protected function setDbConfig(){
        $this->_mysqlConfig  = Application::$_config['mysql'][CUR_ENV];
        $this->_oracleConfig = Application::$_config['oracle'][CUR_ENV];
    }

    /*-----------------------------------------------------------------------------------
    | 加载redis参数配置
    -----------------------------------------------------------------------------------*/
    protected function setRedisConfig(){
        $this->_redisConfig = Application::$_config['redis'][CUR_ENV];
    }

    /*-----------------------------------------------------------------------------------
    | 加载memcache参数配置
    -----------------------------------------------------------------------------------*/
    protected function setMemcacheConfig(){
        $this->_memcacheConfig = Application::$_config['memcache'][CUR_ENV];
    }
        
}

?>


