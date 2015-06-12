<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************
 * 缓存工厂类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

class CacheFactory {
    
    private $_sessionInstance = null;
    private $_redisInstance = null;
    private $_memcacheInstance = null;

    private $_cacheConfig   = null;

    function __construct($Config=null) 
    {
        $this->_cacheConfig = $Config;
    }

    public function __get($cacheName='session') 
    {
        switch (strtolower($cacheName)) {          
            case 'session' :
                if (!isset( $this->_sessionInstance ))
                {
                    $this->_sessionInstance = new BaseSession();
                    return $this->_sessionInstance;
                }
                break;
            case 'redis' :
                if (!isset( $this->_redisInstance ))
                {
                    $this->_redisInstance = BaseRedis::getInstance($this->_cacheConfig);
                    return $this->_redisInstance;
                }
                break;
            case 'memcache' :
                if (!isset( $this->_memcacheInstance ))
                {
                    $this->_memcacheInstance = new BaseMemcache($this->_cacheConfig);
                    return $this->_memcacheInstance;
                }
                break;
            default :
                # code
                break;
        }
    }
        
}


