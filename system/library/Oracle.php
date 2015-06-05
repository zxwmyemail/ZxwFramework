<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/*******************************************************************************************
 * oracle 操作类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 *******************************************************************************************/

class Oracle {

    private $db_host = null;           //数据库主机地址
    private $db_port = null;           //数据库端口
    private $db_server_name = null;    //数据库服务名称
    private $db_user = null;           //数据库账户
    private $db_password = null;       //数据库密码

    private $oci_conn = null;          //当前的数据库连接

    /*-------------------------------------------------------------------------
    | @var  string  当前数据库执行的操作（读/写）
    |       "write":表示数据库正在执行的是写操作，主要影响的函数为oci_execute
    |       "read"：表示数据库正在执行的是读操作
    -------------------------------------------------------------------------*/
    private $oci_mode = null;

    /*-------------------------------------------------------------------------
    | @var  int  数据库执行模式（int oci_execute的mode参数），有两个：
    |       OCI_COMMIT_ON_SUCCESS  
    |       OCI_NO_AUTO_COMMIT
    -------------------------------------------------------------------------*/
    private $oci_execute_mode = OCI_COMMIT_ON_SUCCESS;

    /*-------------------------------------------------------------------------
    | @var boolean  数据库当前是否处于事务当中
    |      true    表示数据库连接处于事务操作中
    |      false   表示数据库连接没有处于事务操作中
    -------------------------------------------------------------------------*/
    private $inTransaction = false;



    /*------------------------------------------------------------------------------------
    | 数据库参数初始化并建立数据库连接
    ------------------------------------------------------------------------------------*/
    function init($db_host,$db_port,$db_server_name,$db_user,$db_password) {

        $this->db_host        = $db_host;
        $this->db_port        = $db_port;
        $this->db_server_name = $db_server_name;
        $this->db_user        = $db_host;
        $this->db_password    = $db_password;

        $this->connect_oci_db();		
    }


    /*----------------------------------------------------------------------------------------
    | 创建数据库连接
    |-----------------------------------------------------------------------------------------
    | @access private
    | @param  null
    |
    | @return boolean :
    |         true 表示成功创建数据库连接
    |         false 表示创建数据库连接失败
    ----------------------------------------------------------------------------------------*/
    private function connect_oci_db() {
        if (is_null($this->oci_conn)) {
            $dbstr = '(DESCRIPTION =(ADDRESS = (PROTOCOL = TCP)(HOST =' . $this->db_host . ')(PORT = ' . $this->db_port . '))(CONNECT_DATA =(SERVICE_NAME = ' . $this->db_server_name . ')))';
            
            $this->oci_conn = oci_connect($this->db_user, $this->db_password, $dbstr, 'AL32UTF8');

            if ($this->oci_conn) {
                return $this->oci_conn;
            } else {
                return false;
            }
        }else{
            return $this->oci_conn;
        }
    }


    /*----------------------------------------------------------------------------------------
    | 根据sql语句决定数据须执行的是读还是写操作
    |----------------------------------------------------------------------------------------
    | @param  string  $sql sql语句
    |
    | @return boolean 是否成功创建数据库连接
    ---------------------------------------------------------------------------------------*/
    private function select_db_connect($sql) {
        $exe_sql = preg_replace("/[ ]+/i", ' ', trim($sql));
        $exe_command = substr($exe_sql, 0, strpos($exe_sql, ' '));

        if (strcasecmp("SELECT", $exe_command) == 0 || strcasecmp("SHOW",$exe_command) == 0)
            $this->oci_mode = 'read';   // SELECT
        else
            $this->oci_mode = 'write';  // INSERT / UPDATE / DELETE</b>

        return $this->connect_oci_db();
    }


    /*---------------------------------------------------------------------------------------------
    | 执行一条sql语句返回结果
    |----------------------------------------------------------------------------------------------
    | @param 变化的参数，主要有以下四种(其中3、4为了兼容老版本，不推荐使用)：
    |        1、string $sql   传入的sql语句（不包含预编译参数）
    |        2、string $sql   传入的sql语句（包含预编译参数）
    |           array  $bind_param 绑定参数的数据，key为预定的变量，value为要绑定的值
    |        3、string $sql   传入的sql语句（包含预编译参数）
    |           string $bind_vars_type 要绑定的参数的类型
    |           string $bind_var1,string $bind_var2... 要绑定的参数1,2,...
    |        4、string $sql   传入的sql语句（包含预编译参数）
    |           string $bind_vars_type 要绑定的参数的类型
    |           array  $bind_param_array 要绑定的参数组成的数据，不指定key，
    |                                    次序按照定义预编译参数的从左到右次序
    | 
    | @return 根据操作类型有不同的返回值：
    |        1、array  执行查询操作返回的结果，每个数据元素是每行结果的一个
    |                  cellbox对象，如果未查到结果将返回 null
    |           如：Array
    |              (
    |                 	[0] => cellBox Object
    |                 	(
    |                      	[_fields:protected] => Array(
    |                           	[UUID] => 41A53BF8E39EB3A48F338F5C90F1A973
    |                           	[TIMESTAMP] => 2012-11-07 17:54:10
    |                           	[CREATETIME] => 2012-11-07 17:54:10
    |                           	[ID] => 1988
    |                           	[NAME] => 509a2fc265f5a
    |                           	[PWD] => D27A0B7FE41491E3B917AEC29E4E590E
    |                           	[RN] => 11
    |                      	)
    |                  	)
    |			[1] => .................
    |			[2] => .................
    |  		  ）
    |         2、boolean  执行写操作返回操作结果，true表示成功，false表示失败     
    ---------------------------------------------------------------------------------------------*/
    public function stmt_query($sql) 
    {
        $this->select_db_connect($sql);

        //将sql中的双引号转为单引号
        $sql = str_replace('"', "'", $sql);

        //如果有limit 组织新的sql
        if (stristr($sql, ' limit ')) {
            $sql_without_limit_cluase = trim(substr($sql, 0,
                            strripos($sql, 'limit')));
            $limit = explode(',',
                    eregi_replace('limit', '',
                            substr($sql, strripos($sql, 'limit'))));
            if (count($limit) == 1) {
                $start = 0;
                $end = $limit[0];
            } else {
                $start = $limit[0] + 1;
                $end = $limit[0] + $limit[1];
            }

            $oci_sql_format = 'SELECT * FROM (SELECT e.*, ROWNUM rn FROM (%s) e WHERE ROWNUM <= %d) WHERE rn >= %d';
            $sql = sprintf($oci_sql_format, $sql_without_limit_cluase, $end,
                    $start);
           
        }

        $params = func_get_args();
        $params_count = count($params);
        $bind_params = array();

        if ($params_count > 2) {
            $bind_vars = array();

            //兼容mysql中用?作为占位符预编译的sql语句
            if (strstr($sql, '?') && is_string($params[1])) {
                $tmpsql_str = '';
                $sql_arr = explode('?', $sql);
                $len = strlen($params[1]);
                if (($len + 1) != count($sql_arr)) {
                    throw new Exception('参数错误:需要绑定的参数数目为' . (count($sql_arr) - 1 ) . ',提供的参数数目为' . $len);
                }
                for ($i = 0; $i < $len; $i++) {
                    $tmpsql_str .= trim($sql_arr[$i]) . ' :' . $i . ' ';
                    $bind_vars[] = ':' . $i;
                }
                $tmpsql_str .= $sql_arr[$len];
                $sql = $tmpsql_str;
                unset($sql_arr);
                unset($tmpsql_str);
            }

            $ref_params = array();
            if (is_array($params[2])) {
                $ref_params = $params[2];
            } else {
                for ($i = 2; $i < $params_count; $i++) {
                    $ref_params[] = $params[$i];
                }
            }
            if(!empty($bind_vars) && !empty($ref_params)){
            	$bind_params = array_combine($bind_vars, $ref_params);
            }
        } elseif ($params_count == 2) {
            $bind_params = $params[1];
        }

        $stmt = oci_parse($this->oci_conn, $sql);
		
        if (!$stmt) {
            throw new Exception(oci_error($this->oci_conn));
        }

        if ($bind_params) {
            foreach ($bind_params as $key => &$val) {
                oci_bind_by_name($stmt, $key, $val);
            }
        }

        $data = array();
        if (!oci_execute($stmt, $this->oci_execute_mode)) {
            throw new Exception(oci_error($this->oci_conn));
        }
        if ($this->oci_mode == 'read') {

            while ($result = oci_fetch_array($stmt, OCI_ASSOC)) {
                $result_record = new CellBox();
                foreach ($result as $k => &$v) {
                    $result_record->$k = $v;
                }
                $data[] = $result_record;

                unset($result_record);
            }
            oci_free_statement($stmt);
            return $data;
        } elseif ($this->oci_mode == 'write') {
            //oci_free_statement($stmt);
            return true;
        }
    }


    /*----------------------------------------------------------------------------------------------
    | 执行一条sql返回结果，不支持预编译方式，此方法兼容性差不推荐使用
    |----------------------------------------------------------------------------------------------
    | @param  string $sql   须执行的sql
    |
    | @return boolean/array 执行结果
    | @throws Exception 
    ---------------------------------------------------------------------------------------------*/
    public function execute($sql) {
        $this->select_db_connect($sql);
        $stmt = oci_parse($this->oci_conn, $sql);

        if (!$stmt) {
            throw new Exception(oci_error($this->oci_conn));
        }
        if (!oci_execute($stmt, $this->oci_execute_mode)) {
            throw new Exception(oci_error($this->oci_conn));
        }
        if ($this->oci_mode == 'read') {
            oci_fetch_all($stmt, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($stmt);
            return $result;
        } elseif ($this->oci_mode == 'write') {
            oci_free_statement($stmt);
            return true;
        }

        return false;
    }


    /*-----------------------------------------------------------------------------
    | 创建statement，继承方法，不推荐使用
    |------------------------------------------------------------------------------
    | @param string $sql 执行的sql语句
    |
    | @return boolean 
    -----------------------------------------------------------------------------*/
    public function createStatement($sql) 
    {
        return oci_parse($this->oci_conn, $sql);
    }


    /*-----------------------------------------------------------------------------
    | 开启事务
    |------------------------------------------------------------------------------
    | @return boolean true/false
    | @throws Exception 
    -----------------------------------------------------------------------------*/
    public function beginTransaction() 
    {
        if ($this->inTransaction)
            throw new Exception('当前数据库连接已存在事务，不能新建事务！');
        $this->oci_execute_mode = OCI_NO_AUTO_COMMIT;
        return $this->inTransaction = true;
    }


    /*-----------------------------------------------------------------------------
    | 提交事务
    |------------------------------------------------------------------------------
    | @return boolean true/false
    | @throws Exception  
    -----------------------------------------------------------------------------*/
    public function commit() 
    {
        if (!$this->inTransaction)
            throw new Exception('当前数据库连接没有发起事务，无法执行事务命令！');
        oci_commit($this->oci_conn);
        $this->oci_execute_mode = OCI_COMMIT_ON_SUCCESS;
        $this->inTransaction = false;
        return true;
    }


    /*-----------------------------------------------------------------------------
    | 回滚事务操作
    |------------------------------------------------------------------------------
    | @return boolean true/false
    | @throws Exception 
    -----------------------------------------------------------------------------*/
    public function rollBack() 
    {
        if (!$this->inTransaction)
            throw new Exception('当前数据库连接没有发起事务，无法执行事务命令！');
        oci_rollback($this->oci_conn);
        $this->oci_execute_mode = OCI_COMMIT_ON_SUCCESS;
        $this->inTransaction = false;
        return true;
    }


    /*-----------------------------------------------------------------------------
    | 当前连接是否在事务当中
    |------------------------------------------------------------------------------
    | @return boolean true/false 
    -----------------------------------------------------------------------------*/
    public function isInTransaction() 
    {
        return $this->inTransaction;
    }


    public function __destruct() 
    {
        if ($this->isInTransaction()) {

            //记录日志信息
            $msg = '数据库存在未决事务';
            $logIns = Log::getInstance();
            $logIns->logMessage($msg,Log::ERROR,'oracle'); 
            
        }
        oci_close($this->oci_conn);
    }

}

?>
