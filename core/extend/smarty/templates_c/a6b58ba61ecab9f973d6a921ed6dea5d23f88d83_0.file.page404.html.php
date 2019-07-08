<?php
/* Smarty version 3.1.33, created on 2019-06-24 18:38:49
  from 'D:\phpStudy\PHPTutorial\WWW\littlephp\app\home\views\home\page404.html' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_5d10a8394feb23_92660450',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'a6b58ba61ecab9f973d6a921ed6dea5d23f88d83' => 
    array (
      0 => 'D:\\phpStudy\\PHPTutorial\\WWW\\littlephp\\app\\home\\views\\home\\page404.html',
      1 => 1561372727,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5d10a8394feb23_92660450 (Smarty_Internal_Template $_smarty_tpl) {
?><!DOCTYPE HTML>  
<html>  
	<head>  
		<meta charset="UTF-8" />  
		<meta name="viewport" content="width=device-width, initial-scale=1">  
		<meta name="robots" content="none" />  
		<title>500 ERROR</title>  
		<link rel="stylesheet" type="text/css" href="asset/css/404.css">  
	</head>  
	<body>  
		<div class="content">   
			<h1>500</h1>  
			<h3>大事不妙啦，请检查系统日志~</h3>  
			<p>原因：你访问的url地址不存在或代码异常报错！</p>  
			<a href="index.php">返回首页 ></a>  
			<img src="?r=home.verifyCode" onclick="this.src='?r=home.verifyCode&'+Math.random();"></img>
		</div>  
	</body>  
</html>
<?php }
}
