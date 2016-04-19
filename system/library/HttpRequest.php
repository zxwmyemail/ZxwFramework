<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************
 * http请求类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

class HttpRequest {

   	/*-----------------------------------------------------------------------------
	| 发起请求
	-----------------------------------------------------------------------------*/
	public function doHttpRequest($method = 'get', $url, $param = array()){
		switch ($method) {
			case 'post':
				return $this->post($url, $param);
				break;
			case 'get':
			default:
				return $this->get($url, $param);
				break;
		}
	}

   	/*-----------------------------------------------------------------------------
	| 发起get请求
	-----------------------------------------------------------------------------*/
	private function get($url, $param=array())
	{
     		if(!is_array($param)){
         		throw new Exception("参数必须为array");
     		}

	     	$p=http_build_query($param);

	     	if(preg_match('/\?[\d\D]+/',$url)){
	         	$p='&'.$p;
	     	}else if(preg_match('/\?$/',$url)){
	         	$p=$p;
	     	}else{
	         	$p='?'.$p;
	     	}
	     	$p=preg_replace('/&$/','',$p);
	     	$url=$url.$p;
	     	//echo $url;
	     	$httph =curl_init($url);
	     	curl_setopt($httph, CURLOPT_SSL_VERIFYPEER, 0);
	     	curl_setopt($httph, CURLOPT_SSL_VERIFYHOST, 1);
	     	curl_setopt($httph,CURLOPT_RETURNTRANSFER,1);
	     	curl_setopt($httph, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
	     
	     	curl_setopt($httph, CURLOPT_RETURNTRANSFER,1);
	     	curl_setopt($httph, CURLOPT_HEADER,1);
	     	$rst=curl_exec($httph);
	     	curl_close($httph);
	     	return $rst;
 	}

   	/*------------------------------------------------------------------------------
	| 发起post请求
	-----------------------------------------------------------------------------*/
 	private function post($url, $param=array())
 	{
	     	if(!is_array($param)){
	         	throw new Exception("参数必须为array");
	     	}
	     	$httph =curl_init($url);
	     	curl_setopt($httph, CURLOPT_SSL_VERIFYPEER, 0);
	     	curl_setopt($httph, CURLOPT_SSL_VERIFYHOST, 1);
	     	curl_setopt($httph,CURLOPT_RETURNTRANSFER,1);
	     	curl_setopt($httph, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
	     	curl_setopt($httph, CURLOPT_POST, 1);
	     	curl_setopt($httph, CURLOPT_POSTFIELDS, $param);
	     	curl_setopt($httph, CURLOPT_RETURNTRANSFER,1);
	     	curl_setopt($httph, CURLOPT_HEADER,1);
	     	$rst=curl_exec($httph);
	     	curl_close($httph);
	     	return $rst;
 	}
}

?>
