<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************
 * 
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 *******************************************************************************************/

class homeModel extends Model{

    function test(){
        echo "this is test homeModel";
    }
    
    function testResult(){
        var_dump($this->mysql);
    }

}


