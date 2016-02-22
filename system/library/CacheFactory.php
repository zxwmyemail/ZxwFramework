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
    
    private $_cacheConfig   = null;

    function __construct($Config=null, $whichCache=null) 
    {
        $this->_cacheConfig = $whichCache ? $Config[$whichCache] : $Config;
    }

    public function __get($cacheName='session') 
    {
        switch ($cacheName) {          
            case 'session' :
                return new BaseSession();
                break;
            case 'redis' :
                return BaseRedis::getInstance($this->_cacheConfig);
                break;
            case 'memcache' :
                return new BaseMemcache($this->_cacheConfig);
                break;
            default :
                # code
                break;
        }
    }
        
}


