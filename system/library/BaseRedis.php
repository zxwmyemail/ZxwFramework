<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************
    BaseRedis类，可以防止惊群现象发生

    防止惊群原理：
    1.伪造一个过期时间，比如为5分钟过期，所以fakeTime = time()+300
    2.真实过期时间，在上面伪造时间基础上多10分钟，也就是15分钟过期，realTime = fakeTime+600
    3.取数据的时候，先判断当前时间是否大于伪造时间：
    4.如果当前时间 time() < fakeTime ，则数据没过期，直接取缓存数据
    5.如果当前时间 fakeTime <= time() < realTime ，第一个人访问时，不设置锁，这种情况下，可以
      去数据库取数据更新缓存，其他人读取时加锁，这种情况下，只能读取旧缓存数据(因为真实时间未
      过，缓存仍有数据)，直到第一个人更新完缓存数据为止。这样就避免了全部人都穿透缓存去数据库
      读取数据而导致的惊群现象！

    配置参数写法：
    $config_redis = array(
        'host'=>'127.0.0.1',
        'auth'=>'123456',
        'port'=>'6379'
    );

    //使用方式
    $redis = Redis::getInstance(config_redis);
*********************************************************************************************/
 
class BaseRedis
{
    private $_redis;                     //redis对象
    private static $_instance = null;    //本类实例
    private static $_cacheTime = 300;    //超时缓冲时间


    private function __construct($config = array())
    {
        if (empty($config)) {
            return false;
        }

        $this->_redis = new Redis();
        $this->_redis->connect($config['server'], $config['port']);
        $this->_redis->auth($config['auth']);
        return $this->redis;
    }


    /*-------------------------------------------------------------------------------------- 
    | 私有化克隆机制
    --------------------------------------------------------------------------------------*/
    private function __clone() {}

 
    /*--------------------------------------------------------------------------------------
    | 获取redis单例
    |---------------------------------------------------------------------------------------
    | @param array $config
    |
    | @return object
    --------------------------------------------------------------------------------------*/
    public static function getInstance($config = array())
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self ($config);
        }
        return self::$_instance;
    }


    /*-------------------------------------------------------------------------------------
    | 获得redis实例
    |--------------------------------------------------------------------------------------
    | @param $key   redis存的key/或随机值
    | @param string $tag   master/slave
    --------------------------------------------------------------------------------------*/
    public function getRedis(){
        return $this->_redis;
    }
     

    /*-------------------------------------------------------------------------------------
    | 获取值
    |--------------------------------------------------------------------------------------
    | @param  string    $key
    |
    | @return array,object,number,string,boolean
    |--------------------------------------------------------------------------------------
    | @desc 此方法使用了锁机制来防止防止缓存过期时所产生的惊群现象，
    |       保证只有一个进程获取数据，可以更新，其他进程仍然获取过期数据
    -------------------------------------------------------------------------------------*/
    public function getByLock($key)
    {
        $sth = $this->_redis->get($key);
        if ($sth === false) {
            return $sth;
        } else {
            $sth = json_decode($sth, TRUE);

            //在伪造期内失效了，但是在真实有效期内有效（只有$key . ".lock"===1为没加锁）
            //第一个人请求，$key . ".lock"===1，为没加锁，返回false，程序去数据库取数据
            //第二个人请求，$key . ".lock"===2，为加锁，直接取缓存旧数据
            //第三个人请求，和上面第二个人一样
            if (intval($sth['expire']) <= time()) {    
                $lock = $this->_redis->incr($key . ".lock");
                if ($lock === 1) {
                    return false;
                } else {
                    return $sth['data'];
                }
            } else {
                return $sth['data'];
            }
        }
    }


    /*-------------------------------------------------------------------------------------------
    | 设置值
    |--------------------------------------------------------------------------------------------
    | @param  string    $key
    | @param  string,array,object,number,boolean    $value 缓存值
    | @param  number $timeOut 过期时间，如果不设置，则使用默认时间，如果为 infinity 则为永久保存
    |
    | @return bool
    |--------------------------------------------------------------------------------------------
    | @desc 此方法会自动加入一些其他数据来避免惊群现象，如需保存原始数据，请使用 set
    -------------------------------------------------------------------------------------------*/
    public function setByLock($key, $value, $timeOut = null)
    {
        $expire = time();
        if (is_numeric($timeOut) && intval($timeOut) > 0) {
            $timeOut = intval($timeOut);
        } else {
            $timeOut = self::$_cacheTime;
        }

        //伪造超时时间
        $fakeExpireTime = time() + $timeOut;    

        //真实超时时间：在原有超时时间上累加缓冲时间得到，使程序有足够的时间生成缓存
        $realExpireTime = $timeOut + self::$_cacheTime;
        
        //制造数据
        $arg = array("data" => $value, "expire" => $fakeExpireTime);

        //设置缓存
        $rs = $this->_redis->setex($key, $realExpireTime, json_encode($arg, TRUE));

        //加锁，加锁情况下，都读取缓存数据
        $this->_redis->del($key . ".lock");

        return $rs;
    }


    /*-------------------------------------------------------------------------------------
    | 删除一条数据 
    |--------------------------------------------------------------------------------------
    | @param string $key KEY名称 
    -------------------------------------------------------------------------------------*/  
    public function delete($key) {  
        return $this->_redis->delete($key);  
    }


    /*-------------------------------------------------------------------------------------
    | 清空数据 
    -------------------------------------------------------------------------------------*/ 
    public function flushAll() {  
        return $this->_redis->flushAll();  
    } 


    /*-------------------------------------------------------------------------------------
    | key是否存在，存在返回ture
    | @param string $key KEY名称
    -------------------------------------------------------------------------------------*/  
    public function exists($key) {  
        return $this->_redis->exists($key);  
    }  

}


?>
