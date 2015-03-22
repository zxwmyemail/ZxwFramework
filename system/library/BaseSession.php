<?php  

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/*******************************************************************************************
 * session类封装
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 *******************************************************************************************/

class BaseSession {  
  
    private static $key_userdata = '|-(+)-|userdata|-(+)-|';  
    private static $key_flashmem = '|-(+)-|flashmem|-(+)-|';  
      
   /*------------------------------------------------------------------------------------------
    | 构造函数，打开session
    -----------------------------------------------------------------------------------------*/
    function __construct(){  
        if (!headers_sent()){ session_start();}  
    }  

    
   /*------------------------------------------------------------------------------------------
    | 析构函数，删除session中的闪存数据
    -----------------------------------------------------------------------------------------*/
    function __destruct(){  
        // 析构函数,删除 flashmem  
        if (isset($_SESSION[self::$key_flashmem])){  
            unset($_SESSION[self::$key_flashmem]);  
        }  
    } 

    
   /*------------------------------------------------------------------------------------------
    | func  : userdata($item) 取session值
    | param : $item 表示一个数组的键
    | return: 返回键所对应的值
    -----------------------------------------------------------------------------------------*/
    function userdata($item){  
        $D = isset($_SESSION[self::$key_userdata]) ? $_SESSION[self::$key_userdata] : FALSE;  
        return $D && is_array($D) && isset($D[$item]) ? $D[$item] : FALSE;  
    }  
      

   /*------------------------------------------------------------------------------------------
    | func  : init_userdata() 初始化session存储数组
    -----------------------------------------------------------------------------------------*/
    private function init_userdata(){  
        if (isset($_SESSION[self::$key_userdata]) && is_array($_SESSION[self::$key_userdata]))  
            return true;  
        $_SESSION[self::$key_userdata] = array();  
    }  
      

   /*------------------------------------------------------------------------------------------
    | func  : set_userdata($newdata = array(), $newval = '') 设置session数据
    |         支持两种设置方式：
    |         一种是直接传入一个数组，数组元素是键值形式
    |         另外一种是传入一个键值对，$newdata键名，$newval为值
    -----------------------------------------------------------------------------------------*/
    function set_userdata($newdata = array(), $newval = ''){  
          
        $this->init_userdata();  
          
        if (is_string($newdata))  
        {  
            $newdata = array($newdata => $newval);  
        }  
  
        if (count($newdata) > 0)  
        {  
            foreach ($newdata as $key => $val)  
            {  
                $_SESSION[self::$key_userdata][$key] = $val;  
            }  
        }  
    }  
    

   /*------------------------------------------------------------------------------------------
    | func  : unset_userdata($newdata = array()) 清除session数据
    |         支持两种清除方式：
    |         一种是直接传入一个数组，数组元素是键值形式
    |         另外一种是传入一个键，$newdata键名
    -----------------------------------------------------------------------------------------*/
    function unset_userdata($newdata = array())  
    {  
          
        $this->init_userdata();  
          
        if (is_string($newdata))  
        {  
            $newdata = array($newdata => '');  
        }  
  
        if (count($newdata) > 0)  
        {  
            foreach ($newdata as $key => $val)  
            {  
                unset($_SESSION[self::$key_userdata][$key]);  
            }  
        }  
    }  
    

   /*----------------------------------------------------------------------------------------
    | func  : all_userdata() 获取所有session的数据
    ---------------------------------------------------------------------------------------*/
    function all_userdata()  
    {  
        return isset($_SESSION[self::$key_userdata]) ? $_SESSION[self::$key_userdata]:FALSE;  
    }  
    

   /*----------------------------------------------------------------------------------------
    | func  : sess_destroy() 销毁session
    ---------------------------------------------------------------------------------------*/
    function sess_destroy(){  
        session_destroy();  
    }     
    

   /*-----------------------------------------------------------------------------------------
    | func  : init_flashdata() 初始化session的闪存存储数组
    ----------------------------------------------------------------------------------------*/
    private function init_flashdata(){  
        if (isset($_SESSION[self::$key_flashmem]) && is_array($_SESSION[self::$key_flashmem]))  
            return true;  
        $_SESSION[self::$key_flashmem] = array();  
    }  
    

   /*-----------------------------------------------------------------------------------------
    | func  : set_flashdata($newdata = array(), $newval = '') 设置session的闪存数据
    |         支持两种设置方式：
    |         一种是直接传入一个数组，数组元素是键值形式
    |         另外一种是传入一个键值对，$newdata键名，$newval为值
    ----------------------------------------------------------------------------------------*/
    function set_flashdata($newdata = array(), $newval = '')  
    {  
        $this->init_flashdata();  
          
        if (is_string($newdata))  
        {  
            $newdata = array($newdata => $newval);  
        }  
  
        if (count($newdata) > 0)  
        {  
            foreach ($newdata as $key => $val)  
            {  
                $_SESSION[self::$key_flashmem][$key] = $val;  
            }  
        }  
    }  
      

   /*----------------------------------------------------------------------------------------
    | func  : flashdata($item) 取session的闪存数据
    | param : $item 表示一个数组的键
    | return: 返回键所对应的值
    ---------------------------------------------------------------------------------------*/
    function flashdata($item)  
    {  
        $D = isset($_SESSION[self::$key_flashmem]) ? $_SESSION[self::$key_flashmem] : FALSE;  
        return $D && is_array($D) && isset($D[$item]) ? $D[$item] : FALSE;  
    }  
      
}  

?>
