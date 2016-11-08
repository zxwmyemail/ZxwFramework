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
	public function doHttpRequest($method = 'get', $url, $param = array(), $timeout=30, $header = 0){
		switch ($method) {
			case 'post':
				return $this->post($url, $param, $timeout, $header);
				break;
			case 'get':
			default:
				return $this->get($url, $param, $timeout, $header);
				break;
		}
	}

   	/*-----------------------------------------------------------------------------
	| 发起get请求
	-----------------------------------------------------------------------------*/
	private function get($url, $param, $timeout, $header)
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
	     	curl_setopt($httph, CURLOPT_SSL_VERIFYHOST, 2);
	     	curl_setopt($httph, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
	     	curl_setopt($httph, CURLOPT_RETURNTRANSFER,1);
	     	curl_setopt($httph, CURLOPT_HEADER, $header);
	     	curl_setopt($httph, CURLOPT_TIMEOUT,$timeout);
	     	$rst=curl_exec($httph);
	     	curl_close($httph);
	     	return $rst;
 	}

   	/*------------------------------------------------------------------------------
	| 发起post请求
	-----------------------------------------------------------------------------*/
 	private function post($url, $param, $timeout, $header)
 	{
	     	if(!is_array($param)){
		    throw new Exception("参数必须为array");
		}
		$httph =curl_init($url);
		curl_setopt($httph,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($httph, CURLOPT_TIMEOUT,$timeout);
		curl_setopt($httph, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($httph, CURLOPT_SSL_VERIFYHOST, 2);
		if (!empty($param)) {
		    curl_setopt($httph, CURLOPT_POST, 1);
		    curl_setopt($httph, CURLOPT_HTTPHEADER, array('Expect:'));
		    curl_setopt($httph, CURLOPT_POSTFIELDS, $param);
		}
		curl_setopt($httph, CURLOPT_HEADER,$header);
		$output=curl_exec($httph);
		curl_close($httph);
		return $output;
 	}
	
	/*------------------------------------------------------------------------------
	| 发起带有请求头的post请求，头样式：
	| $headerData[] = "Connection: keep-alive"; 
	| $headerData[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	-----------------------------------------------------------------------------*/
	public function postWithHeaderData($url, $param=array(), $headerData = [], $timeout=30, $header=0)
	{
		if(!is_array($param)){
		    throw new Exception("参数必须为array");
		}
		$httph =curl_init($url);
		curl_setopt($httph,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($httph, CURLOPT_TIMEOUT,$timeout);
		curl_setopt($httph, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($httph, CURLOPT_SSL_VERIFYHOST, 2);

		$httpHeader = array(); 
		if (!empty($param)) {
		    curl_setopt($httph, CURLOPT_POST, 1);
		    $httpHeader[] = 'Expect:';
		    curl_setopt($httph, CURLOPT_POSTFIELDS, $param);
		}

		$httpHeader = array_merge($httpHeader, $headerData);
		if (!empty($httpHeader)) {
		    curl_setopt($httph, CURLOPT_HTTPHEADER, $httpHeader);
		}
		curl_setopt($httph, CURLOPT_HEADER, $header);
		$output=curl_exec($httph);
		curl_close($httph);
		return $output;
	}
}

?>

