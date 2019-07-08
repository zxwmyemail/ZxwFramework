<?php
/*******************************************************************************************
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 *******************************************************************************************/
namespace core\system;

class Config {

    /*--------------------------------------------------------------------------------------
    | 获取配置文件
    |---------------------------------------------------------------------------------------
    | @access  final   public
    | @param   string  $fileName    配置文件名（对应tinyphp/config文件夹下的文件）
    | @param   string  $subName     二级配置key值
    --------------------------------------------------------------------------------------*/
    public static function get($fileName = 'config', $subName = ''){
        if (empty($fileName)) {
            return false;
        }

        $fileName = ltrim($fileName, '/');
        $fileName = ltrim($fileName, '\\');

        $configPath = CONFIG_PATH . DS . $fileName . '.php';
        $configPath = str_replace('\\', DIRECTORY_SEPARATOR, $configPath);

        if (file_exists($configPath)) {
            $config = require($configPath);
            if (empty($subName)) {
                return $config;
            } else {
                return isset($config[$subName]) ? $config[$subName] : false;
            }
        }

        return false;
    }
        
}

?>


