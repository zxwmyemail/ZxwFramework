<?php
/*******************************************************************************************
 * 公用方法类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 *******************************************************************************************/
namespace core\system;

use core\library\BaseSession;
use core\library\BaseRedis;
use core\library\HashRedis;

trait Common {

    private $_hashRedis;
    
    /*---------------------------------------------------------------------------------------
    | 获取缓存实例
    |----------------------------------------------------------------------------------------
    | @access  final   public
    | @param   string  $name          缓存名字：session、redis、hashRedis
    | @param   string  $whichCache    哪一个缓存：
    |                                 如果获取redis的master实例：$whichCache = 'master';
    ----------------------------------------------------------------------------------------*/
    public function getCache($name = 'session', $whichCache = 'master'){
        switch ($name) {
            case 'session':
                return new BaseSession();
                break;
            case 'redis':
                $redisConf = Config::get('redis', $whichCache);
                return BaseRedis::getInstance($redisConf, $whichCache);
                break;
            case 'hashRedis':
                if (empty($this->_hashRedis)) {
                    $hashRedisConf = Config::get('hashRedis');
                    $this->_hashRedis = new HashRedis($hashRedisConf);
                }
                return $this->_hashRedis;
                break;
            default:
                echo '参数错误';exit();
                break;
        }
    }
        
}

?>


