<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************
 * 常用工具类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

class Util {

   /*------------------------------------------------------------------------------
    | XOR算法加密
    -----------------------------------------------------------------------------*/
    public static function encrypt($string, $key) {
        $str_len = strlen($string);
        $key_len = strlen($key);
        for ($i = 0; $i < $str_len; $i++) {
            for ($j = 0; $j < $key_len; $j++) {
                $string[$i] = $string[$i] ^ $key[$j];
            }
        }
        return $string;
    }


   /*------------------------------------------------------------------------------
    | XOR算法解密
    -----------------------------------------------------------------------------*/
    public static function decrypt($string, $key) {
        $str_len = strlen($string);
        $key_len = strlen($key);
        for ($i = 0; $i < $str_len; $i++) {
            for ($j = 0; $j < $key_len; $j++) {
                $string[$i] = $key[$j] ^ $string[$i];
            }
        }
        return $string;
    }


   /*------------------------------------------------------------------------------
    | 获得概率随机值
    | @praram $proArr  概率数组，如 array('0'=>30,'1'=>40,'2'=>20,'3'=>10)
    | @return $result  被随机到的数组索引
    -----------------------------------------------------------------------------*/
    public static function get_rand($proArr) { 
        $result = ''; 

        //概率数组的总概率精度 
        $proSum = array_sum($proArr); 

        //概率数组循环 
        foreach ($proArr as $key => $proCur) {
            //抽取随机数 
            $randNum = mt_rand(1, $proSum);             
            if ($randNum <= $proCur) {
                //得出结果 
                $result = $key;                         
                break; 
            } else { 
                $proSum -= $proCur;                     
            } 
        } 
        unset ($proArr); 
        return $result; 
    }   

   /*------------------------------------------------------------------------------
    | 获得客户端真实的IP地址
    -----------------------------------------------------------------------------*/
    public static function getClientIp()
    {
        $ip = "unknown";

        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
            $ip = getenv("HTTP_CLIENT_IP");
        }else{
            if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
                $ip = getenv("HTTP_X_FORWARDED_FOR");
            } else {
                if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
                    $ip = getenv("REMOTE_ADDR");
                } else {
                    if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
                        $ip = $_SERVER['REMOTE_ADDR'];
                    } else {
                        $ip = "unknown";
                    }
                }
            }
        }

        return ($ip);
    }


   /*------------------------------------------------------------------------------
    | 获得随机码
    -----------------------------------------------------------------------------*/
    public static function random($length, $isNum = FALSE)
    {
        $random = '';
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $num = '0123456789';
        if ($isNum)
            $sequece = 'num';
        else
            $sequece = 'str';
        $max = strlen($$sequece) - 1;
        for ($i = 0; $i < $length; $i++)
        {
            $random .= ${$sequece}{mt_rand(0, $max)};
        }
        return $random;
    }


   /*-----------------------------------------------------------------------------
    | 字符截取函数
    | 
    | @param String  $text 内容
    | @param Integer $limit 截取长度
    | @param String  $add 更多标记
    -----------------------------------------------------------------------------*/
    public static function cut_str($text, $limit, $add = '&#8230;',$db_charset='utf-8')
    {
        $strlen = strlen($text);
        $db_charset = strtolower($db_charset);
        if($strlen <= $limit) return $text;
        $rtext = '';
        if ($db_charset == 'utf-8')
        {
            $n = $tn = $noc = 0;
            while ($n < $strlen)
            {
                $t = ord($text{$n});
                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)){
                    $tn = 1; $n++; $noc++;
                }elseif (194 <= $t && $t <= 223){
                    $tn = 2; $n += 2; $noc += 2;
                }elseif (224 <= $t && $t < 239){
                    $tn = 3; $n += 3; $noc += 2;
                }elseif (240 <= $t && $t <= 247){
                    $tn = 4; $n += 4; $noc += 2;
                }elseif (248 <= $t && $t <= 251){
                    $tn = 5; $n += 5; $noc += 2;
                }elseif ($t == 252 || $t == 253){
                    $tn = 6; $n += 6; $noc += 2;
                }else{
                    $n++;
                }
                if($noc >= $limit) break;
            }
            if($noc > $limit) $n -= $tn;
            $rtext = substr($text, 0, $n);
        }
        else
        {
            $addlen = strlen($add);
            $limit -= $addlen - 1;
            for ($i = 0; $i < $limit; $i++){
                $rtext .= ord($text[$i]) > 127 ? $text[$i] . $text[++$i] : $text[$i];
            }
        }
        return $rtext.$add;
    }


   /*-----------------------------------------------------------------------------
    | 创建图片缩略图片
    | create images thumb  
    | @param string $img
    | @param int $height
    | @param int $width
    | @param string  $save_prefix
    | @param bool $del
    ----------------------------------------------------------------------------*/
    public static function thumb($img, $height, $width, $save_prefix = 'thumb_', $del = false)
    {
        if (empty($img) || !gdEnable() || !isImg($img)) return $img;
        $imginfo = @getimagesize($img);
        switch($imginfo[2])
        {
            case 1:
                $tmp_img = @imagecreatefromgif($img);
                break;
            case 2:
                $tmp_img = imagecreatefromjpeg($img);
                break;
            case 3:
                $tmp_img = imagecreatefrompng($img);
                break;
            default:
                $tmp_img = imagecreatefromstring($img);
                break;
        }
        if ($save_prefix)
        {
            $imgpath = substr($img, 0, strrpos($img, '/'));
            $filename = substr($img, strrpos($img, '/')+1);
            $savepath = $imgpath.'/'.$save_prefix.$filename;
        }
        else
        {
            $savepath = $img;
        }
        if(($height >= $imginfo[1] || !$height) && ($width >= $imginfo[0] || !$width))
        {
            if ($save_prefix)
            {
                @copy($img, $savepath) || PWriteFile($savepath, PReadFile($img), 'wb');
                $del && LK_del($img);
            }
            return array($savepath, floor($imginfo[1]), floor($imginfo[0]));
        }
        $realscale = $imginfo[1] / $imginfo[0];
        if ($realscale <= 1)
        {
            $width = ($width > $imginfo[0] || !$width) ? $imginfo[0] : $width;
            $height = ($height > $imginfo[1] || !$height) ? $imginfo[1] : $width*$realscale;
        }
        else
        {
            $height = ($height > $imginfo[1] || !$height) ? $imginfo[1] : $height;
            $width = ($width > $imginfo[0] || !$width) ? $imginfo[0] : $height / $realscale;
        }
        $width = floor($width);
        $height = floor($height);
        $dst_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($dst_image, $tmp_img, 0, 0, 0, 0, $width, $height, $imginfo[0], $imginfo[1]);
         switch($imginfo[2])
        {
            case '1':
                imagegif($dst_image, $savepath);
                break;
            case '2':
                imagejpeg($dst_image, $savepath);
                break;
            case '3':
                imagepng($dst_image, $savepath);
                break;
            default :
                imagejpeg($dst_image, $savepath);
                break;
        }   
        $save_prefix && $del && LK_del($img);
        return array($savepath, $height, $width);
    }


   /*------------------------------------------------------------------------------
    | 获得两个时间段之间的时间差
    -----------------------------------------------------------------------------*/
    public static function DateDiff($part, $begin, $end){
        $diff = (is_numeric($end) ? $end : strtotime($end)) - (is_numeric($begin) ? $begin : strtotime($begin));
        switch ($part){
            case "y": $retval = bcdiv($diff, (60 * 60 * 24 * 365)); break;
            case "m": $retval = bcdiv($diff, (60 * 60 * 24 * 30)); break;
            case "w": $retval = bcdiv($diff, (60 * 60 * 24 * 7)); break;
            case "d": $retval = bcdiv($diff, (60 * 60 * 24)); break;
            case "h": $retval = bcdiv($diff, (60 * 60)); break;
            case "n": $retval = bcdiv($diff, 60); break;
            case "s": $retval = $diff; break;
        }
        return $retval;
    }


   /*-------------------------------------------------------------------------------
    | 防止sql注入检查 
    ------------------------------------------------------------------------------*/
    public static function inject_check($sql_str) {
        $check = eregi('select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile', $sql_str);
        if ($check) {
            echo "输入非法注入内容！";
            exit ();
        } else {
            return $sql_str;
        }
    }


   /*-------------------------------------------------------------------------------
    | 制造数据库唯一ID，类似自增键值
    ------------------------------------------------------------------------------*/
    public static function make_uuid() {
        $address = strtolower ( 'localhost' . '/' . '127.0.0.1' );
        list ( $usec, $sec ) = explode ( " ", microtime () );
        $time = $sec . substr ( $usec, 2, 3 );
        $random = rand ( 0, 1 ) ? '-' : '';
        $random = $random . rand ( 1000, 9999 ) . rand ( 1000, 9999 ) . rand ( 1000, 9999 ) . rand ( 100, 999 ) . rand ( 100, 999 );
        $uuid = strtoupper ( md5 ( $address . ':' . $time . ':' . $random ) );
        $uuid = substr ( $uuid, 0, 8 ) . '-' . substr ( $uuid, 8, 4 ) . '-' . substr ( $uuid, 12, 4 ) . '-' . substr ( $uuid, 16, 4 ) . '-' . substr ( $uuid, 20 );
        $uuid = str_replace ( "-", "", $uuid );
        return $uuid;
    }


   /*------------------------------------------------------------------------------
    | 创建路径
    -----------------------------------------------------------------------------*/
    public static function mkdir($var, $basedir='', $force = FALSE)
    {
        if (strpos($var, '..') !== FALSE || strpos($basedir, '..') !== FALSE)
        {
            return false;
            //exit('Access Denied!');
        }

        if (!is_dir($basedir.$var))
        {
            //$var = preg_replace('/\/{2,}/', '/', str_replace('\\', '/', $var));
            //$basedir = preg_replace('/\/{2,}/', '/', str_replace('\\', '/', $basedir));
            $temp = explode(DIRECTORY_SEPARATOR,$var);
            $dirnum = count($temp);
            $cur_dir = '';

            for($i = 0; $i < $dirnum; $i++)
            {
                $cur_dir .= $temp[$i].DIRECTORY_SEPARATOR;
                if (!is_dir($cur_dir))
                {
                    if (!mkdir($cur_dir, 0777) && $force)
                    {
                        return false;
                        //showMsg('attachment_mkdir_failed');
                    }
                }

            }
        }
        return TRUE;
    }

    
}

?>
