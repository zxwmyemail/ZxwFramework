<?php
namespace core\library;
/********************************************************************************************
 * http请求类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

class HttpCurl {

   	/*-----------------------------------------------------------------------------
	| 发起get请求
	-----------------------------------------------------------------------------*/
	public static function get($url, $options = [], $timeout = 30, $header = 0)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
 	}

   	/*------------------------------------------------------------------------------
	| 发起post请求
	-----------------------------------------------------------------------------*/
 	public static function post($url, $postData = '', $options = [], $timeout = 30, $header = 0)
 	{
 		if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        if (!empty($postData)) {
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		}
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
 	}
	
	/*------------------------------------------------------------------------------
	| 发起带有请求头的post请求，头样式：
	| $headerData[] = "Connection: keep-alive"; 
	| $headerData[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	-----------------------------------------------------------------------------*/
	public static function postWithHeader($url, $postData = '', $headerData = [], $timeout = 30, $header = 0)
	{
		$httph = curl_init($url);
		curl_setopt($httph,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($httph, CURLOPT_TIMEOUT,$timeout);
		curl_setopt($httph, CURLOPT_HEADER, $header);
		curl_setopt($httph, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($httph, CURLOPT_SSL_VERIFYHOST, false);

		$httpHeader = array(); 
		if (!empty($postData)) {
		    curl_setopt($httph, CURLOPT_POST, 1);
		    $httpHeader[] = 'Expect:';
		    curl_setopt($httph, CURLOPT_POSTFIELDS, $postData);
		}

		$httpHeader = array_merge($httpHeader, $headerData);
		if (!empty($httpHeader)) {
		    curl_setopt($httph, CURLOPT_HTTPHEADER, $httpHeader);
		}
		
		$output = curl_exec($httph);
		curl_close($httph);
		return $output;
	}
}

?>

