<?php
namespace core\extend\smarty;
/********************************************************************************************
 Excel表格类，github地址： 
 https://github.com/smarty-php/smarty
*********************************************************************************************/
use core\system\Application;

class ZFSmarty {

    /*---------------------------------------------------------------------------------------
    | 设置smarty的路径配置参数
    |----------------------------------------------------------------------------------------
    | @access      public
    ----------------------------------------------------------------------------------------*/
    public function getSmarty()
    {
        $routeInfo = Application::$_routeParams;

        $module     = $routeInfo['module'];
        $controller = $routeInfo['controller'];

        $smarty = new \Smarty; 
        
        //设置各个目录的路径，这里是配置smarty的路径参数
        $smarty->template_dir    = APP_PATH . DS  . $module . DS . 'views' . DS . $controller;
        $smarty->compile_dir     = CORE_PATH . DS  . "extend/smarty" . DS . "templates_c";
        $smarty->config_dir      = CORE_PATH . DS  . "extend/smarty" . DS . "config";
        $smarty->cache_dir       = CORE_PATH . DS  . "extend/smarty" . DS . "cache";
        $smarty->left_delimiter  = "<{";
        $smarty->right_delimiter = "}>";

        //smarty模板有高速缓存的功能，如果这里是true的话即打开caching
        //但是会造成网页不立即更新的问题，当然也可以通过其他的办法解决
        $smarty->caching = false; 

        return $smarty;
    }
    
}

