<?php
/*******************************************************************************************
 * URL处理类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 *******************************************************************************************/
namespace core\system;

final class Route {

    private static $_reqParams = [];
    private static $_routeParams = [];

    /*-------------------------------------------------------------------------------------
    | 获取数组形式的URL  
    |--------------------------------------------------------------------------------------
    | @access      public
    -------------------------------------------------------------------------------------*/
    public static function handleParams() {
        $routeConf = Config::get('config', 'route');

        $result = [];
        switch ($routeConf['use_pathinfo']){
            case 1:
                $result = self::parseCommonRoute($routeConf);
                break;
            case 2:
                $result = self::parsePathinfoRoute($routeConf);
                break;
            default:
                throw new \Exception('config配置文件中route端，use_pathinfo值配置错误');
        }

        return $result;
    }

    /*-------------------------------------------------------------------------------------
    | 普通路由获取参数
    |--------------------------------------------------------------------------------------
    | @access      public
    -------------------------------------------------------------------------------------*/
    public static function parseCommonRoute($routeConf) {

        if (isset($_REQUEST['m'])) {
            self::$_routeParams['module'] = $_REQUEST['m'];
            unset($_REQUEST['m']);
        } else {
            self::$_routeParams['module'] = $routeConf['default_module'];
        }

        if (isset($_REQUEST['r'])) {
            $route = explode('.', $_REQUEST['r']);
            self::$_routeParams['controller'] = $route[0];
            self::$_routeParams['action'] = $route[1];
            unset($_REQUEST['r']);
        } else {
            self::$_routeParams['controller'] = $routeConf['default_controller'];
            self::$_routeParams['action'] = $routeConf['default_action'];
        } 
        
        self::$_reqParams = $_REQUEST;

        return [
            'request' => self::$_reqParams,
            'route'   => self::$_routeParams
        ];
    }

    /*-------------------------------------------------------------------------------------
    | pathinfo获取路由参数
    |--------------------------------------------------------------------------------------
    | @access      public
    -------------------------------------------------------------------------------------*/
    public static function parsePathinfoRoute($routeConf) {

        $uri = $_SERVER['REQUEST_URI'];
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        $pathInfo = trim($uri, '/');
        $pathInfo = empty($pathInfo) ? [] : explode('/', $pathInfo);

        if (!empty($pathInfo)){
            // 获取 module
            if (count($pathInfo) > 2 && isset($pathInfo[0])) {
                self::$_routeParams['module'] = $pathInfo[0];
                array_shift($pathInfo);
            } else {
                self::$_routeParams['module'] = $routeConf['default_module'];
            }

            // 获取 controller
            if (count($pathInfo) > 0 && isset($pathInfo[0])) {
                self::$_routeParams['controller'] = $pathInfo[0];
                array_shift($pathInfo);
            } else {
                self::$_routeParams['controller'] = $routeConf['default_controller'];
            }
            
            // 获取 action
            if (count($pathInfo) > 0 && isset($pathInfo[0])) {
                self::$_routeParams['action'] = $pathInfo[0];
                array_shift($pathInfo);
            } else {
                self::$_routeParams['action'] = $routeConf['default_action'];
            }
        } else {
            self::$_routeParams = [
                'module'     => $routeConf['default_module'],
                'controller' => $routeConf['default_controller'],
                'action'     => $routeConf['default_action'],
            ];
        }

        self::$_reqParams = $_REQUEST; 

        return [
            'request' => self::$_reqParams,
            'route'   => self::$_routeParams
        ];
    }

}


