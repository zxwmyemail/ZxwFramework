<?php
namespace app\home\controllers;

use core\system\Controller;

class Base extends Controller
{
    /**
     * 返回json字符串信息
     *
     * @param int        $retcode 	 结果编码
     * @param string     $retmsg 	 结果说明
     * @param array      $data 	     返回数据
     */
    public function json($retcode, $retmsg, $data = '')
    {
        $this->renderJSON([
            'retcode' => $retcode,
            'retmsg'  => $retmsg,
            'data'    => $data
        ]);
    }

    
}