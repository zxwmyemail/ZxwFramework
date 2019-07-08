<?php
namespace core\library;
/********************************************************************************************
redis的一致性hash类
@copyright   Copyright(c) 2015
@author      iProg
@version     1.0

使用方法：
$redis_servers = array(
       array(
             'host'       => '10.0.0.1',
             'port'       => 6379,
      ),
       array(
             'host'       => '10.0.0.2',
             'port'       => 6379,
      ),
       array(
             'host'       => '10.0.0.3',
             'port'       => 6379,
      ),
       array(
             'host'       => '10.0.0.3',
             'port'       => 6928,
      ),
);

$HashRedis = new HashRedis($redis_servers);
$testKey = 'test_key';
$testValue = 'test_value_object';
$HashRedis->setValue($testKey, $testValue, 3600);
$value = $HashRedis->getValue($testKey);
*******************************************************************************************/

class HashRedis {
    public $servers = array();        //真实服务器
    private $_servers = array();      //虚拟节点
    private $_serverKeys = array();
    private $_badServers = array();   //故障服务器列表
    private $_count = 0;

    const SERVER_REPLICAS = 10000;    //服务器副本数量，提高一致性哈希算法的数据分布均匀程度
   
    public function __construct( $servers ){
        $this->servers = $servers;
        $this->_count = count($this-> servers);

        //Redis虚拟节点哈希表
        foreach ($this ->servers as $k => $server) {
            for ($i = 0; $i < self::SERVER_REPLICAS; $i++) {
                $hash = crc32($server[ 'host'] . '#' .$server['port'] . '#'. $i);
                $this->_servers [$hash] = $k;
            }
        }
        ksort( $this->_servers );
        $this->_serverKeys = array_keys($this-> _servers);
    }
   
    /**
     * 使用一致性哈希分派服务器，附加故障检测及转移功能
     */    
    private function getRedis($key){
        $hash = crc32($key);
        $slen = $this->_count * self:: SERVER_REPLICAS;

        // 快速定位虚拟节点
        $sid = $hash > $this->_serverKeys [$slen-1] ? 0 : $this->quickSearch($this->_serverKeys, $hash, 0, $slen);

        $conn = false;
        $i = 0;
        do {
            $n = $this->_servers [$this->_serverKeys[$sid]];
            !in_array($n, $this->_badServers ) && $conn = $this->getRedisConnect($n);
            $sid = ($sid + 1) % $slen;
        } while (!$conn && $i++ < $slen);
       
        return $conn ? $conn : new \Redis();
    }
   
    /**
     * 二分法快速查找
     */
    private function quickSearch($stack, $find, $start, $length) {
        if ($length == 1) {
            return $start;
        }
        else if ($length == 2) {
            return $find <= $stack[$start] ? $start : ($start +1);
        }
       
        $mid = intval($length / 2);
        if ($find <= $stack[$start + $mid - 1]) {
            return $this->quickSearch($stack, $find, $start, $mid);
        }
        else {
            return $this->quickSearch($stack, $find, $start+$mid, $length-$mid);
        }
    }
   
    private function getRedisConnect($n=0){
        static $REDIS = array();
        if (!$REDIS[$n]){
            $REDIS[$n] = new \Redis();
            try{
                $ret = $REDIS[$n]->pconnect( $this->servers [$n]['host'], $this->servers [$n]['port']);
                if (!$ret) {
                    unset($REDIS[$n]);
                    $this->_badServers [] = $n;
                    return false;
                }
            } catch(Exception $e){
                unset($REDIS[$n]);
                $this->_badServers [] = $n;
                return false;
            }
        }
        return $REDIS[$n];
    }
   
    public function getValue($key){
        try{
            $getValue = $this->getRedis($key)->get($key);
        } catch(Exception $e){
            $getValue = null;
        }

       return $getValue;
    }
   
    public function setValue($key,$value,$expire){
        if($expire == 0){
            try{
                $ret = $this->getRedis($key)->set($key, $value);
            } catch(Exception $e){
                $ret = false;
            }
        } else{
            try{
                $ret = $this->getRedis($key)->setex($key, $expire, $value);
            } catch(Exception $e){
                $ret = false;
            }
        }
        return $ret;
    }
   
    public function deleteValue($key){
        return $this->getRedis($key)->delete($key);
    }
   
    public function flushValues(){
        //TODO
        return true;
    }
}

?>
