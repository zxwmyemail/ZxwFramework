<?php  
if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');
/*******************************************************************************************
 * session类封装
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

class BaseSession {  
  
    private static $key_session_data = '|-(+)-|userdata|-(+)-|';     // 持久session
    private static $key_flashmem_data = '|-(+)-|flashmem|-(+)-|';    // 闪存session，只对本次请求有效
      
    /*------------------------------------------------------------------------------------------
    | 构造函数，打开session
    ------------------------------------------------------------------------------------------*/
    public function __construct(){  
        if (!headers_sent()){ session_start();}  
    }  

    /*------------------------------------------------------------------------------------------
    | 析构函数，删除session中的闪存数据
    ------------------------------------------------------------------------------------------*/
    public function __destruct(){  
        // 析构函数,删除 flashmem  
        if (isset($_SESSION[self::$key_flashmem_data])){  
            unset($_SESSION[self::$key_flashmem_data]);  
        }  
    } 

    /*------------------------------------------------------------------------------------------
    | 初始化session存储数组
    ------------------------------------------------------------------------------------------*/
    private function _initSessionData(){  
        if (isset($_SESSION[self::$key_session_data]) && is_array($_SESSION[self::$key_session_data]))  
            return true;  
        $_SESSION[self::$key_session_data] = array();  
    }  

    /*------------------------------------------------------------------------------------------
    | 取session值
    |-------------------------------------------------------------------------------------------
    | param : $item 表示一个数组的键
    |
    | return: 返回键所对应的值
    -------------------------------------------------------------------------------------------*/
    public function getSessionData($item){  
        $D = isset($_SESSION[self::$key_session_data]) ? $_SESSION[self::$key_session_data] : FALSE;  
        return $D && is_array($D) && isset($D[$item]) ? $D[$item] : FALSE;  
    }  
    
    /*------------------------------------------------------------------------------------------
    | 设置session数据
    | 支持两种设置方式：
    | 1.直接传入一个数组，数组元素是键值形式
    | 2.传入一个键值对，$newdata键名，$newval为值
    ------------------------------------------------------------------------------------------*/
    public function setSessionData($newdata = array(), $newval = '')
    {  
        $this->_initSessionData();  
          
        if (is_string($newdata))  
        {  
            $newdata = array($newdata => $newval);  
        }  
  
        if (count($newdata) > 0)  
        {  
            foreach ($newdata as $key => $val)  
            {  
                $_SESSION[self::$key_session_data][$key] = $val;  
            }  
        }  
    }  
    
    /*------------------------------------------------------------------------------------------
    | 清除session数据
    | 支持两种清除方式：
    | 1.直接传入一个数组，数组元素是键值形式
    | 2.传入一个键，$newdata键名
    ------------------------------------------------------------------------------------------*/
    public function deleteSessiondata($newdata = array())  
    {  
        $this->_initSessionData();  
          
        if (is_string($newdata))  
        {  
            $newdata = array($newdata => '');  
        }  
  
        if (count($newdata) > 0)  
        {  
            foreach ($newdata as $key => $val)  
            {  
                unset($_SESSION[self::$key_session_data][$key]);  
            }  
        }  
    }  
    
    /*----------------------------------------------------------------------------------------
    | 获取所有session的数据
    ----------------------------------------------------------------------------------------*/
    public function getAllSessionData()  
    {  
        return isset($_SESSION[self::$key_session_data]) ? $_SESSION[self::$key_session_data]:FALSE;  
    }  
    
    /*----------------------------------------------------------------------------------------
    | func  : destroySession() 销毁session
    ----------------------------------------------------------------------------------------*/
    public function destroySession(){  
        session_destroy();  
    }     
    
    /*-----------------------------------------------------------------------------------------
    | 初始化session的闪存存储数组
    -----------------------------------------------------------------------------------------*/
    private function _initFlashData(){  
        if (isset($_SESSION[self::$key_flashmem_data]) && is_array($_SESSION[self::$key_flashmem_data]))  
            return true;  
        $_SESSION[self::$key_flashmem_data] = array();  
    }  
    
    /*-----------------------------------------------------------------------------------------
    | 设置session的闪存数据
    | 支持两种设置方式：
    | 1.直接传入一个数组，数组元素是键值形式
    | 2.传入一个键值对，$newdata键名，$newval为值
    ------------------------------------------------------------------------------------------*/
    public function setFlashData($newdata = array(), $newval = '')  
    {  
        $this->_initFlashData();  
          
        if (is_string($newdata))  
        {  
            $newdata = array($newdata => $newval);  
        }  
  
        if (count($newdata) > 0)  
        {  
            foreach ($newdata as $key => $val)  
            {  
                $_SESSION[self::$key_flashmem_data][$key] = $val;  
            }  
        }  
    }  
      
    /*----------------------------------------------------------------------------------------
    | 取session的闪存数据
    |-----------------------------------------------------------------------------------------
    | param : $item 表示一个数组的键
    | return: 返回键所对应的值
    -----------------------------------------------------------------------------------------*/
    public function getFlashData($item)  
    {  
        $D = isset($_SESSION[self::$key_flashmem_data]) ? $_SESSION[self::$key_flashmem_data] : FALSE;  
        return $D && is_array($D) && isset($D[$item]) ? $D[$item] : FALSE;  
    }  
      
}  

?>
