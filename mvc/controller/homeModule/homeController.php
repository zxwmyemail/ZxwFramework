<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

class homeController extends Controller {
        
    public function __construct() {
        parent::__construct();
    }

    public function page404Action() {
        $this->smarty->display('404Page.html'); 
        exit();
    }

    public function indexAction() {

        //测试smarty模板
        $this->smarty->assign('name','iProg');
        $this->smarty->display('home.html'); 
        exit();
        
        //测试重定向
        $this->redirect('page404'); 
        exit();

        //测试日志记录函数
        $logIns = Log::getInstance();
        $logIns->logMessage("test",Log::INFO,'myTest'); 
        die();

        //测试手动加载类函数
        $model = Application::newObject('MyTest','public');
        var_dump($model);
        die();


        //测试静态类
        $result = Util::get_rand(array('0'=>30,'1'=>40,'2'=>20,'3'=>10));
        var_dump($result);die();


        //测试自定义类加载，可自动加载mvc/model/、system/library/、system/core/这些文件夹下面的类
        $homeModel = new homeModel();
        $homeModel -> testResult();
        exit();

    }
}

