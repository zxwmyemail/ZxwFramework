<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************
 * MySql 操作类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 *******************************************************************************************/

class MySQL {

	private $dbhost;                	//数据库地址
	private $dbname;             		//数据库名称
	private $username;           		//用户名
	private $password;           		//密码
	private $pconnect;           		//是否持久连接
	private $dblink = null;      		//连接	
	private $resource = null;    		//资源
	private $records = null;     		//记录
	private $insert_id;          		//添加数据ID
	private $queries = 0;        		//查询次数
	private $prefix ='';         		//表前缀
	private $db_charset='utf-8'; 		//表编码

	private $_LKENV = array(            //错误信息是否调试显示
				'debug' => true
	        );	                        


	//返回记录类型，类型包括MYSQL_ASSOC，MYSQL_NUM 和 MYSQL_BOTH
	private $resultType = MYSQL_ASSOC;

	////错误提示信息数组
	private $dbErrMsg = array (
		'db_errno_1040' => '数据库已到达最大连接数',
		'db_errno_1044' => '当前用户没有访问数据库的权限',
		'db_errno_1045' => '无法连接数据库，请检查数据库用户名或者密码是否正确',
		'db_errno_1046' => '数据表不存在',
		'db_errno_1049' => '数据库不存在',
		'db_errno_1054' => '数据库字段不存在',
		'db_errno_1064' => 'SQL执行发生错误',	
		'db_errno_2003' => '无法连接数据库，请检查数据库是否启动，数据库服务器地址是否正确',
		'unknowError'   => 'SQL执行发生错误'
	);
	
	
	public function init($db_host, $db_username, $db_password, $db_name, $db_charset, $db_pconnect = 0) 
	{
		$this->db_charset = $db_charset;
		$this->dbhost     = $db_host;
		$this->dbname     = $db_name;
		$this->username   = $db_username;
		$this->password   = $db_password;
		$this->pconnect   = $db_pconnect;

		$this->connect();
	}


  /*-----------------------------------------------------------------------------
	| 获取数据库连接实例
	------------------------------------------------------------------------------*/
	public function connect()
	{
		if ($this->pconnect) {
			$this->dblink = @ mysql_pconnect ( $this->dbhost, $this->username, $this->password );
			if (! $this->dblink) {
				$this->db_halt ();
			}
		} else {
			$this->dblink = @ mysql_connect ( $this->dbhost, $this->username, $this->password );
			if (! $this->dblink) {
				$this->db_halt ();
			}
		}
		if ($this->db_version () > '4.1') {
			$db_charset = strtolower ( $this->db_charset );
			if ($db_charset && in_array ( $db_charset, array ('gbk', 'big5', 'utf-8' ) )) {
				if (strpos ( $db_charset, '-' ) !== false) {
					$db_charset = str_replace ( '-', '', $db_charset );
				}
				mysql_query ( "SET character_set_client = 'binary', character_set_connection = '" . $db_charset . "', character_set_results = '" . $db_charset . "'", $this->dblink );
			}
		}
		if ($this->db_version () > '5.0.1') {
			mysql_query ( "SET sql_mode = ''", $this->dblink );
		}
		if ($this->dbname) {
			$this->db_select ();
		}
	}

	
  /*-----------------------------------------------------------------------------
	| 执行查询语句
	------------------------------------------------------------------------------*/
	public function db_query($sql, $mod = 1) 
	{
		$t = microtime(1);      //时间
		if ($mod) {
			$this->resource = mysql_query ( $sql, $this->dblink );
		} elseif ($mod == 0 && function_exists ( 'mysql_unbuffered_query' )) {
			$this->resource = mysql_unbuffered_query ( $sql, $this->dblink );
		}
		$this->queries ++;
		if (! $this->resource) {
			$this->db_halt ( $sql );
		}
		$time = microtime(1)-$t;
		if($time>0.1){
			$msg = date('Y-m-d H:i:s').":: 执行时间  ".$time." :: ".$sql."\r\n";

			//记录日志信息
			$logIns = Log::getInstance();
        	$logIns->logMessage($msg,Log::ERROR,'mysql'); 

		}
		return  $this->resource;
	}


  /*-----------------------------------------------------------------------------
	| 取得关联数据集
	| 
	| @param resource $query
	-----------------------------------------------------------------------------*/
	public function fetch_all($query)
	{
		return mysql_fetch_array($query, $this->resultType);
	}
	

  /*-----------------------------------------------------------------------------
	| 取得关联数据集
	| 
	| @return Array() 记录集
	-----------------------------------------------------------------------------*/
	public function db_fetch_array() 
	{
		return mysql_fetch_array ( $this->resource, $this->resultType );
	}
	

  /*-----------------------------------------------------------------------------
	| 取得数字数据集
	-----------------------------------------------------------------------------*/
	public function db_fetch_row() 
	{
		return mysql_fetch_row ( $this->resource );
	}
	

  /*-----------------------------------------------------------------------------
	| 取得一行记录
	| 
	| @return Array() 返回一条记录
	-----------------------------------------------------------------------------*/
	public function db_fetch_one_array($sql) 
	{
		$this->db_query ( $sql );
		return $this->db_fetch_array ();
	}


  /*----------------------------------------------------------------------------
	| 取得多行记录
	| 
	| @return Array() 返回一条记录
	----------------------------------------------------------------------------*/
	public function fetch_all_array($sql) 
	{
		$this->db_query ( $sql );
		while ( $rs = $this->db_fetch_array () ) {
			$ra [] = $rs;
		}
		return $ra;		
	}


  /*---------------------------------------------------------------------------
	| 取得select语句查询结果的数目
	---------------------------------------------------------------------------*/
	public function db_num() 
	{
		return mysql_num_rows ( $this->resource );
	}
	

  /*----------------------------------------------------------------------------
	| 取得 INSERT，UPDATE，DELETE 语句查询结果的数目
	----------------------------------------------------------------------------*/
	public function db_affected() 
	{
		return mysql_affected_rows ( $this->dblink );
	}
	

  /*----------------------------------------------------------------------------
	| 取得结果集中字段的数目
	----------------------------------------------------------------------------*/
	public function db_fieldNum() 
	{
		return mysql_num_fields ( $this->resource );
	}
	

  /*---------------------------------------------------------------------------
	| 取得上一步 INSERT 操作产生的 ID
	----------------------------------------------------------------------------*/
	public function db_insertID() 
	{
		return mysql_insert_id ( $this->dblink );
	}
	

  /*---------------------------------------------------------------------------
	| 选择数据库
	---------------------------------------------------------------------------*/
	public function db_select() 
	{
		if (! mysql_select_db ( $this->dbname, $this->dblink )) {
			$this->db_halt ();
		}
	}
	

  /*---------------------------------------------------------------------------
	| 释放资源
	---------------------------------------------------------------------------*/
	public function db_free() 
	{
		return mysql_free_result ( $this->resource );
	}
	

  /*---------------------------------------------------------------------------
	| 关闭连接
	---------------------------------------------------------------------------*/
	public function db_close() 
	{
		return mysql_close ( $this->dblink );
	}
	

  /*---------------------------------------------------------------------------
	| 数据库版本
	---------------------------------------------------------------------------*/
	public function db_version() 
	{
		return mysql_get_server_info ( $this->dblink );
	}
	

  /*---------------------------------------------------------------------------
	| 返回 MySQL 操作产生的文本错误信息
	---------------------------------------------------------------------------*/
	public function db_errorMsg() 
	{
		return mysql_error ( $this->dblink );
	}
	

  /*---------------------------------------------------------------------------
	| 返回 MySQL 操作中的错误信息的数字编码
	---------------------------------------------------------------------------*/
	public function db_errorNo() 
	{
		return mysql_errno ( $this->dblink );
	}
	

  /*---------------------------------------------------------------------------
	| 设置查询返回类型
	---------------------------------------------------------------------------*/
	public function setResultType($resultType) 
	{
		$this->resultType = $resultType;
	}
	

  /*---------------------------------------------------------------------------
	| 返回资源
	---------------------------------------------------------------------------*/
	public function getResource() 
	{
		return $this->resource;
	}


  /*---------------------------------------------------------------------------
	| 设置资源
	---------------------------------------------------------------------------*/
	public function setResource($resource) 
	{
		if (is_resource ( $resource )) {
			$this->resource = $resource;
			return TRUE;
		} else {
			return FALSE;
		}
	}


  /*---------------------------------------------------------------------------
	| 返回查询次数
	---------------------------------------------------------------------------*/
	public function getQueries() 
	{
		return $this->queries;
	}


  /*--------------------------------------------------------------------------
	| 取得记录数数
	| 
	| @param String $condition 查询条件
	| @param String $table 表名
	---------------------------------------------------------------------------*/
	public function countRecords($table, $condition = '') 
	{
		$SQL = "SELECT count(*) num FROM {$this->prefix}{$table}";
		if ($condition) {
			$SQL .= " WHERE {$condition}";
		}
		//echo $SQL;die;
		$result = $this->db_fetch_one_array ( $SQL );
		return $result ['num'];
	}
	

  /*---------------------------------------------------------------------------
	| 更新记录
	| 
	| @param Array $filed 字段值，array('字段名' => '值')
	| @param String $condition 更新条件
	---------------------------------------------------------------------------*/
	public function updateRecords($field = array(), $table, $condition = '') 
	{
		if (empty ( $field ) || ! is_array ( $field )) {
			return FALSE;
		}
		$SQL = "UPDATE {$this->prefix}{$table} SET ";
		foreach ( $field as $key => $value ) {
			if (strpos ( $value, '[!--selffiled--]' ) !== FALSE) {
				$value = str_replace ( '[!--selffiled--]', $key, $value );
				$SQL .= "$key = $value,";
			} else {
				$SQL .= "$key = '$value',";
			}
		}
		$SQL = substr ( $SQL, 0, - 1 );
		if ($condition) {
			$SQL .= " WHERE $condition";
		}	
		$result = $this->db_query ( $SQL );
		return  $result;
	}
	

  /*---------------------------------------------------------------------------
	| 取得记录集，返回数组
	| 
	| @param Array $filed 字段值，array('字段名')
	| @param String $table 表名
	| @param String $condition 更新条件
	---------------------------------------------------------------------------*/
	public function getFiledValues($field = array(), $table, $condition = '') 
	{	
		$ra = array ();
		if (is_array($field)) {
			$field = implode ( ',', $field );
		} else {
			$field = '*';
		}
		$SQL = "SELECT {$field} FROM {$this->prefix}{$table}";
		if ($condition) {
			$SQL .= " WHERE $condition";
		}
		//echo $SQL."<br>";die;
		$this->db_query ( $SQL );
		while ( $rs = $this->db_fetch_array () ) {
			$ra [] = $rs;
		}
		return $ra;
	}


  /*-----------------------------------------------------------------------------
	| 增加新记录
	| 
	| @param Array $item 字段记录关联数组 array('filed'=>value)
	| @param String $table 表名
	-----------------------------------------------------------------------------*/
	public function addRecords($item = array(), $table) 
	{		
		$field = $fieldValue = '';
		$SQL = "INSERT INTO {$this->prefix}{$table} (%s) 
			VALUES(%s)";
		if ($item && is_array ( $item )) {
			foreach ( $item as $key => $value ) {
				$field .= "{$key},";
				$fieldValue .= "'{$value}',";
			}
			$field = substr ( $field, 0, - 1 );
			$fieldValue = substr ( $fieldValue, 0, - 1 );
			$SQL = sprintf ( $SQL, $field, $fieldValue );
		}	
		$result = $this->db_query ( $SQL );
		$this->insert_id =  mysql_insert_id();
		return $result;
	}
	

  /*-----------------------------------------------------------------------------
	| 删除记录
	| 
	| @param String $condition 查询条件
	| @param String $table 表名
	-----------------------------------------------------------------------------*/
	public function delRecords($table, $condition = '') 
	{
		$SQL = "DELETE FROM {$this->prefix}{$table}";
		if ($condition) {
			$SQL .= " WHERE {$condition}";
		}
		$result = $this->db_query ( $SQL );
		return $result;
	}
		
	
  /*----------------------------------------------------------------------------
	| 取得字段值
	| 
	| @param Array $filed 字段值，array('字段名')
	| @param String $table 表名
	| @param String $condition 更新条件
	----------------------------------------------------------------------------*/
	public function getSingleFiledValues($field = array(), $table, $condition = '') 
	{	
		if ($field) {
			$field = implode ( ',', $field );
		} else {
			$field = '*';
		}
		$SQL = "SELECT {$field} FROM {$this->prefix}{$table}";
		if ($condition) {
			$SQL .= " WHERE $condition";
		}
		//echo $SQL."<br />";
		$SQL .= ' limit 1';
		$rs = $this->db_fetch_one_array ( $SQL );
		return $rs;
	}
	

  /*---------------------------------------------------------------------------
	| 数据库错误提示显示页面
	----------------------------------------------------------------------------*/
	public function db_halt($sql = '') 
	{
		global $_DEV;
		echo $sql;
		echo mysql_error ();
		$this->showError ( $sql );
		exit ();
	}
	

  /*------------------------------------------------------------------------------
	| 显示错误信息页面
	------------------------------------------------------------------------------*/
	public function showError($sql = '') 
	{
		$db_charset = $this->db_charset;
		
		$msg = $this->$dbErrMsg ['db_errno_' . $this->db_errorNo ()];
		if (! $msg) {
			$msg = $this->$dbErrMsg ['unknowError'];
		}
		$message = "<html>\n<head>\n";
		$message .= "<meta content=\"text/html; charset=$db_charset\" http-equiv=\"Content-Type\">\n";
		$message .= "</head>\n";
		$message .= "<body>\n";
		$message .= "<p style=\"font-family: Verdana, Tahoma; font-size: 11px; background: #fff;\">\n";
		if ($this->_LKENV ['debug']) {
			$message .= "<strong>55like Info：</strong>" . $msg . "\n<br />";
			$message .= "<strong>Time：</strong>" . date ( 'Y-m-d H:i:s' ) . "\n<br />";
			if ($sql) {
				$message .= "<strong>SQL：</strong><code>" . $sql . "</code>\n<br />";
			}
			$message .= "<strong>MySQL Error Description：</strong>" . $this->db_errorMsg () . "\n<br />";
			$message .= "<strong>MySQL Error Number：</strong>" . $this->db_errorNo () . "\n<br />";
		} else {
			$message .= "<strong>55like Info：</strong>" . $msg . "\n<br />";
		}
		$message .= "</p>\n";
		$message .= "</body>\n</html>";
		echo $message;
		exit ();
	}
	

  /*------------------------------------------------------------------------------
	| 下拉选项功能 
	------------------------------------------------------------------------------*/
	public function html_options($table, $where, $t=-1, $selectId=0)
	{
		$query = $this->db_query("SELECT * FROM $table WHERE $where ORDER BY id ASC");
		$rows = $optionNav = '';
		while($res = $this->fetch_all($query)){
			$rows[] = $res; 
		}
		$t++;  
		$countNav=count($rows)-1;  // 最后的
		$nbsp=str_repeat('&nbsp;',$t*3);
		if($rows){
			foreach($rows as $key=>$val){
				if($key == $countNav && $val['id'] == $selectId){   // 判断是否最后一个
					$optionNav .='<option value="'.$val['id'].'" selected="selected">'.$nbsp.'┗'.$val['name'].'</option>';
				}elseif($val['id'] == $selectId){
					$optionNav .='<option value="'.$val['id'].'" selected="selected">'.$nbsp.'┣'.$val['name'].'</option>';						
				}elseif($key == $countNav){
					$optionNav .='<option value="'.$val['id'].'">'.$nbsp.'┗'.$val['name'].'</option>';
				}else{
					$optionNav .='<option value="'.$val['id'].'">'.$nbsp.'┣'.$val['name'].'</option>';
				}
				$optionNav .=$this->html_options($table,'pid='.$val['id'], $t, $selectId);  // 递归这个方法的本身
			}
		}
		return $optionNav;	
	}
}

?>
