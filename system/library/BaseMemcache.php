<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/*********************************************************************************************
    CLASS :  BaseMemcache类
    AUTHOR:  iProg 
    DATE  :  2015-03-09

    配置文件的写法
    $_CONFIG['system']['memcache'] = array (
        'server'        =>  array(
            array(
               'host'   => '127.0.0.1',
               'port'   => '11211', 
               'weight' => 1
            ),
            array(
               'host'   => '127.0.0.1',
               'port'   => '11211', 
               'weight' => 1
            )
        ),
        'expiration'    =>  18600,   //过期时间
        'prefix'        =>  'zxw_',  //键值前缀
        'compression'   =>  false,   //使用MEMCACHE_COMPRESSED标记对数据进行压缩(使用zlib)。
        'isAutoTresh'   =>  ture,    //是否开启大值自动压缩
        'threshold'     =>  20000,   //控制多大值进行自动压缩的阈值
        'thresavings'   =>  0.2      //指定经过压缩实际存储的值的压缩率
                                     //支持的值必须在0和1之间。默认值是0.2表示20%压缩率。
    );
********************************************************************************************/

class BaseMemcache {  
      
    public $memcacheInstance = null;
    private $memcacheType = null; 
    private $config = array();
    private $localCache = array();
    protected $errors = array();


    /*---------------------------------------------------------------------------------------
    | 创建memcache对象
    |----------------------------------------------------------------------------------------
    | @param $memcacheConfig  
    |        服务器参数配置数组，如：
    |        array(
    |            array('host' => '127.0.0.1','port' => '11211', 'weight' => 1),
    |            array('host' => '127.0.0.1','port' => '11211', 'weight' => 1)
    |        )
    ---------------------------------------------------------------------------------------*/
    public function __construct($memcacheConfig)
    {
        $this->config = $memcacheConfig;

        $serverConfig = $memcacheConfig['server'];

        $isMemcached = class_exists('Memcached') ? "Memcached" : FALSE;
        $this->memcacheType = class_exists('Memcache') ? "Memcache" : $isMemcached;
         
        if($this->memcacheType) {
            switch($this->memcacheType) {
                case 'Memcached':
                    $this->memcacheInstance = new Memcached();
                    break;
                case 'Memcache':
                    $this->memcacheInstance = new Memcache();
                    if ($this->config['isAutoTresh']){
                        $this->setCompressThreshold($this->config['threshold'], $this->config['thresavings']);
                    }
                    break;
            }
            $this->createConnect($serverConfig);  
        } else {
            echo 'ERROR: Failed to load Memcached or Memcache Class (∩_∩)';
            exit;
        }
    } 


    /*---------------------------------------------------------------------------------------
    | 创建memcache链接
    ---------------------------------------------------------------------------------------*/
    private function createConnect($serverConfig)
    { 
        if (!is_array($serverConfig) || empty($serverConfig)) {
            exit('Memcache server is null!');  
        }

        foreach ($serverConfig as $val) { 
            extract($val);
            $this->memcacheInstance->addServer($host, $port, $weight);  
        }
    }

          
    /*------------------------------------------------------------------------------------
    | 添加键值
    |-------------------------------------------------------------------------------------
    | @param  : $key        键
    | @param  : $value      值
    | @param  : $expiration 过期时间
    |
    | @return : TRUE or FALSE
    ------------------------------------------------------------------------------------*/
    public function add($key = NULL, $value = NULL, $expiration = 0)
    {
        if(is_null($expiration)){
            $expiration = $this->config['expiration'];
        }

        if(is_array($key)){

            foreach($key as $multi){
                if(!isset($multi['expiration']) || $multi['expiration'] == ''){
                    $multi['expiration'] = $this->config['expiration'];
                }
                $this->add($multi['key'], $multi['value'], $multi['expiration']);
            }

        } else {

            $key = $this->MD5key($key);
            $this->localCache[$key] = $value;

            switch($this->memcacheType){
                case 'Memcache':
                    $flag = $this->memcacheInstance->add($key, $value, $this->config['compression'], $expiration);
                    break;
                case 'Memcached':
                    $flag = $this->memcacheInstance->add($key, $value, $expiration);
                    break;
                default:
                    break;
            }
             
            return $flag;
        }
    }

     
    /*-------------------------------------------------------------------------------------
    | 添加键值，与add函数类似，但服务器有此键值时仍可写入替换
    |--------------------------------------------------------------------------------------
    | @param    $key          键
    | @param    $value        值
    | @param    $expiration   过期时间
    |
    | @return   TRUE or FALSE
    -------------------------------------------------------------------------------------*/
    public function set($key = NULL, $value = NULL, $expiration = NULL)
    {
        if(is_null($expiration)){
            $expiration = $this->config['expiration'];
        }

        if(is_array($key)) {

            foreach($key as $multi){
                if(!isset($multi['expiration']) || $multi['expiration'] == ''){
                    $multi['expiration'] = $this->config['expiration'];
                }
                $this->set($multi['key'], $multi['value'], $multi['expiration']);
            }

        }else{

            $key = $this->MD5key($key);
            $this->localCache[$key] = $value;

            switch($this->memcacheType){
                case 'Memcache':
                    $flag = $this->memcacheInstance->set($key, $value, $this->config['compression'], $expiration);
                    break;
                case 'Memcached':
                    $flag = $this->memcacheInstance->set($key, $value, $expiration);
                    break;
                default:
                    break;
            }

            return $flag;
        }
    }
     

    /*------------------------------------------------------------------------------------
    | 根据键名获取值，取值函数
    |-------------------------------------------------------------------------------------
    | @param  $key  键
    | @return array OR json object OR string...
    -------------------------------------------------------------------------------------*/
    public function get($key = NULL)
    {
        if(is_null($key)) {
            $this->errors[] = 'The key value cannot be NULL';
            return false;
        }

        if($this->memcacheInstance)
        {
            if(isset($this->localCache[$this->MD5key($key)]))
            {
                return $this->localCache[$this->MD5key($key)];
            }

            if(is_array($key)){
                foreach($key as $n=>$k){
                    $key[$n] = $this->MD5key($k);
                }
                return $this->memcacheInstance->getMulti($key);
            }else{
                return $this->memcacheInstance->get($this->MD5key($key));
            }
        }else{
            return false;
        }      
    }
     

    /*-----------------------------------------------------------------------------------
    | 删除键值
    |------------------------------------------------------------------------------------
    | @param  $key          key
    | @param  $expiration   服务端等待删除该元素的总时间
    |
    | @return true OR false
    -----------------------------------------------------------------------------------*/
    public function delete($key, $expiration = NULL)
    {
        if(is_null($key))
        {
            $this->errors[] = 'The key value cannot be NULL';
            return false;
        }
         
        if(is_null($expiration))
        {
            $expiration = $this->config['expiration'];
        }
         
        if(is_array($key))
        {
            foreach($key as $multi)
            {
                $this->delete($multi, $expiration);
            }
        }
        else
        { 
            $key = $this->MD5key($key);
            unset($this->localCache[$key]);
            return $this->memcacheInstance->delete($key, $expiration);
        }
    }
     

    /*---------------------------------------------------------------------------------------
    | replace替换键值
    |----------------------------------------------------------------------------------------
    | @param  $key        要替换的key
    | @param  $value      要替换的value
    | @param  $expiration 到期时间
    |
    | @return none
    ----------------------------------------------------------------------------------------*/
    public function replace($key = NULL, $value = NULL, $expiration = NULL)
    {
        if(is_null($expiration)){
            $expiration = $this->config['expiration'];
        }
        if(is_array($key)){

            foreach($key as $multi) {
                if(!isset($multi['expiration']) || $multi['expiration'] == ''){
                    $multi['expiration'] = $this->config['expiration'];
                }
                $this->replace($multi['key'], $multi['value'], $multi['expiration']);
            }

        }else{

            $key = $this->MD5key($key);
            $this->localCache[$key] = $value;
             
            switch($this->memcacheType){
                case 'Memcache':
                    $flag = $this->memcacheInstance->replace($key, $value, $this->config['compression'], $expiration);
                    break;
                case 'Memcached':
                    $flag = $this->memcacheInstance->replace($key, $value, $expiration);
                    break;
                default:
                    break;
            }
             
            return $flag;
        }
    }
     

    /*----------------------------------------------------------------------------------------
    | 清空所有缓存
    ----------------------------------------------------------------------------------------*/
    public function flush()
    {
        return $this->memcacheInstance->flush();
    }
     

    /*----------------------------------------------------------------------------------------
    | 获取服务器池中所有服务器的版本信息
    ----------------------------------------------------------------------------------------*/
    public function getVersion()
    {
        return $this->memcacheInstance->getVersion();
    }
     
     
    /*----------------------------------------------------------------------------------------
    | 获取服务器池的统计信息
    ----------------------------------------------------------------------------------------*/
    public function getStats($type = 'items')
    {
        $stats = null;

        switch($this->memcacheType)
        {
            case 'Memcache':
                $stats = $this->memcacheInstance->getStats($type);
                break;
            case 'Memcached':
                $stats = $this->memcacheInstance->getStats();
                break;
        }

        return $stats;
    }
     

    /*----------------------------------------------------------------------------------------------
    | 开启大值自动压缩
    |-----------------------------------------------------------------------------------------------
    | @param  : $tresh   控制多大值进行自动压缩的阈值。
    | @param  : $savings 指定经过压缩实际存储的值的压缩率，值必须在0和1之间。默认值0.2表示20%压缩率。
    |
    | @return : true OR false
    ----------------------------------------------------------------------------------------------*/
    public function setCompressThreshold($tresh, $savings=0.2)
    {
        $flag = false;
        switch($this->memcacheType)
        {
            case 'Memcache':
                $flag = $this->memcacheInstance->setCompressThreshold($tresh, $savings);
                break;
            default:
                $flag = TRUE;
                break;
        }
        return $flag;
    }
     
    /*----------------------------------------------------------------------------------------
    | 生成md5加密后的唯一键值
    |-----------------------------------------------------------------------------------------
    | @param  : $key      key
    |
    | @return : string    md5($key)
    ----------------------------------------------------------------------------------------*/
    private function MD5key($key)
    {
        $key = isset($this->config['prefix']) ? $this->config['prefix'].$key : $key;
        return md5(strtolower($key));
    }
     

    /*-------------------------------------------------------------------------------------
    | 向已存在元素后追加数据
    |--------------------------------------------------------------------------------------
    | @param  : $key      key
    | @param  : $value    value
    |
    | @return : true OR   false
    -------------------------------------------------------------------------------------*/
    public function append($key = NULL, $value = NULL)
    {
        $key = $this->MD5key($key);
        $this->localCache[$key] = $value;
         
        switch($this->memcacheType)
        {
            case 'Memcache':
                $flag = $this->memcacheInstance->append($key, $value);
                break;
            case 'Memcached':
                $flag = $this->memcacheInstance->append($key, $value);
                break;
        }
         
        return $flag;
    }  
}  

?>
