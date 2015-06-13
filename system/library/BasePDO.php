<?php 

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************* 
   数据库PDO操作 ： BasePDO 
   Author : iProg
   Date   : 2015-03-12
*********************************************************************************************/ 

class BasePDO { 
  
    private $_pdo = null;                        //pdo实例
    private $_pdoStmt = null;                    //pdo执行资源实例 

    private static $_pdoInstance = null;         //单例模式，保存本类实例       


    /*--------------------------------------------------------------------------------------- 
    | 公共静态方法获取实例化的对象
    ---------------------------------------------------------------------------------------*/  
    public static function getInstance($pdoConfig = '') 
    {  
        if (!(self::$_pdoInstance instanceof self)) {  
            self::$_pdoInstance = new self($pdoConfig);  
        }  
        return self::$_pdoInstance;  
    }  


    /*---------------------------------------------------------------------------------------
    | 构造函数
    |---------------------------------------------------------------------------------------- 
    | @param $dbconfig 	数据库连接配置相关信息
    --------------------------------------------------------------------------------------- */ 
    private function __construct($pdoConfig='') 
    {  
	if (!class_exists('PDO')) throw new Exception("环境不支持:PDO");

	if (empty($pdoConfig)) throw new Exception("PDO数据库参数异常!");

        try {  
            $this->_pdo = new PDO($pdoConfig['dns'], $pdoConfig['username'], $pdoConfig['password']);  

            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

            if($pdoConfig['pconnect']) { 
			$this->_pdo->setAttribute(PDO::ATTR_PERSISTENT, TRUE); 
	    }

            $this->_pdo->query('SET NAMES ' . $pdoConfig['dbcharset']);

        } catch (PDOException $e) {  
            exit($e->getMessage());  
        }

        unset($pdoConfig);   
    }  

     
    /*-------------------------------------------------------------------------------------- 
    | 私有化克隆机制
    --------------------------------------------------------------------------------------*/
    private function __clone() {}


    /*-------------------------------------------------------------------------------------- 
    | 添加一条记录
    |---------------------------------------------------------------------------------------
    | @param    string  $table   表名
    | @param    array   $data    array('表字段名'=>'表字段值','表字段名'=>'表字段值');
    |
    | @return   int     影响记录数
    --------------------------------------------------------------------------------------*/  
    public function addOne($table, $data) 
    { 
    	if (empty($table) || empty($data) || !is_array($data)) {
    		throw new Exception("添加一条记录函数传递的参数异常", 1);
    	}

        $addFields = array_keys($data);  
        $addValues = array_values($data);   

        $addFields = "(`" . implode('`,`', $addFields) . "`)";  
        $addValues = "('" . implode("','", $addValues) . "')";

        $sql = "INSERT INTO {$table} {$addFields} VALUES {$addValues}";  
        return $this->execSQL($sql);  
    }


    /*-------------------------------------------------------------------------------------- 
    | 添加多条记录
    |---------------------------------------------------------------------------------------
    | @param  string  $table  表名
    | @param  array   $data   array(
    |                       	    array('表字段名'=>'表字段值','表字段名'=>'表字段值',......),
    |				    array('表字段名'=>'表字段值','表字段名'=>'表字段值',......)
    |                         );
    |
    | @return int     影响记录数
    --------------------------------------------------------------------------------------*/  
    public function addMany($table, $data) 
    { 
    	if (empty($table) || empty($data) || !is_array($data)) {
    		throw new Exception("添加多条记录函数传递的参数异常", 1);
    	}

    	$addFields = array_keys($data[0]); 

        $addValues = array();
        foreach ($data as $value) {
          	$addValues[] = "('".implode("','",array_values($value))."')";
        }  

        $addFields = "(`" . implode('`,`', $addFields) . "`)"; 
        $addValues = implode(",", $addValues);

        $sql = "INSERT INTO {$table} {$addFields} VALUES {$addValues}";  
        return $this->execSQL($sql);  
    }


    /*-------------------------------------------------------------------------------------- 
    | 执行sql语句
    |---------------------------------------------------------------------------------------
    | 
    | @param   string  $sql  sql语句，写法有两种，举例如下：
    |						 1. select * from where id=:id and name=:name ;
    |						 2. select * from where id=? and name=? ;
    |
    | @param   array   $params  对应上面sql语句中的参数，具体写法如下：
    |						 1. array(':id' => 1, ':name' => 'zxw');
    |						 2. array(1 => 1, 2 => 'zxw');
    |
    | @return  如果是insert、update、delete语句，返回影响记录数
    |	       如果是select语句，返回结果集
    --------------------------------------------------------------------------------------*/   
    public function execSQL($sql='',$params=array()) 
    {
    	if($this->isMainIps($sql)) { 
		return $this->execute($sql, $params=array()); 
	} else { 
		return $this->fetchAll($sql, $params=array()); 
	} 
    }


    /*--------------------------------------------------------------------------------------
    | 执行查询 获取所有数据 
    |---------------------------------------------------------------------------------------
    | @param   string  $sql  sql语句，写法有两种，举例如下：
    |						 1. select * from where id=:id and name=:name ;
    |						 2. select * from where id=? and name=? ;
    | @param   array   $params  对应上面sql语句中的参数，具体写法如下：
    |						 1. array(':id' => 1, ':name' => 'zxw');
    |						 2. array(1 => 1, 2 => 'zxw');
    |
    | @return  返回结果集 
    --------------------------------------------------------------------------------------*/ 
    public function fetchAll($sql='', $params=array()) 
    { 
	$this->select($sql, $params);

        //返回数据集 
	return $this->_pdoStmt->fetchAll(PDO::FETCH_ASSOC); 
    }  


    /*---------------------------------------------------------------------------------------
    | 获得一条查询结果 
    |----------------------------------------------------------------------------------------
    | @param   string  $sql  sql语句，写法有两种，举例如下：
    |						 1. select * from where id=:id and name=:name ;
    |						 2. select * from where id=? and name=? ;
    | @param   array   $params  对应上面sql语句中的参数，具体写法如下：
    |						 1. array(':id' => 1, ':name' => 'zxw');
    |						 2. array(1 => 1, 2 => 'zxw');
    |
    | @return  返回结果集
    --------------------------------------------------------------------------------------- */ 
    public static function fetchOne($sql='', $params=array()) 
    { 
	$this->select($sql, $params);

	// 返回数组集 
	return $this->_pdoStmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
    }


    /*---------------------------------------------------------------------------------------
    | 获得查询结果的条数
    |----------------------------------------------------------------------------------------
    | @param   string  $sql  sql语句，写法有两种，举例如下：
    |                        1. select count(*) from where id=:id and name=:name ;
    |                        2. select count(*) from where id=? and name=? ;
    | @param   array   $params  对应上面sql语句中的参数，具体写法如下：
    |                        1. array(':id' => 1, ':name' => 'zxw');
    |                        2. array(1 => 1, 2 => 'zxw');
    |
    | @return  返回结果集条数
    --------------------------------------------------------------------------------------- */ 
    public static function getRowCount($sql='', $params=array()) 
    { 
        $this->select($sql, $params);

        // 返回数组集条数
        return $this->_pdoStmt->fetchColumn();
    }


    /*-------------------------------------------------------------------------------------
    | 获取最后一条插入数据的ID
    |-------------------------------------------------------------------------------------- 
    | @return 最后一条插入数据的ID
    -------------------------------------------------------------------------------------*/ 
    public function getLastInsertId() 
    { 
        return $this->_pdo->lastInsertId(); 
    } 


    /*--------------------------------------------------------------------------------------
    | 执行语句 针对 INSERT, UPDATE 以及DELETE 
    |---------------------------------------------------------------------------------------
    | @param   string  $sql  sql语句，写法有两种，举例如下：
    |						 1. select * from where id=:id and name=:name ;
    |						 2. select * from where id=? and name=? ;
    | @param   array   $params  对应上面sql语句中的参数，具体写法如下：
    |						 1. array(':id' => 1, ':name' => 'zxw');
    |						 2. array(1 => 1, 2 => 'zxw');
    |
    | @return  返回影响记录数
    --------------------------------------------------------------------------------------*/ 
    public function execute($sql='', $params=array()) 
    {
	$this->select($sql, $params);
        return $this->_pdoStmt->rowCount(); 
    }


    /*-------------------------------------------------------------------------------------
    | 执行查询SELECT指令 
    |--------------------------------------------------------------------------------------
    | @param   string  $sql  sql语句，写法有两种，举例如下：
    |						 1. select * from where id=:id and name=:name ;
    |						 2. select * from where id=? and name=? ;
    | @param   array   $params  对应上面sql语句中的参数，具体写法如下：
    |						 1. array(':id' => 1, ':name' => 'zxw');
    |						 2. array(1 => 1, 2 => 'zxw'); 
    | @return  无 
    --------------------------------------------------------------------------------------*/ 
    private function select($sql='', $params=array()) 
    { 
	try {  
           //释放前次的查询结果 
	    if ( !empty($this->_pdoStmt)) $this->_pdoStmt = null;

            $this->_pdoStmt = $this->_pdo->prepare($sql); 

            if (!empty($params)) {
            	foreach ($params as $key => $value) {
            		$this->_pdoStmt->bindParam($key, $value);
            	}
            }
            $this->_pdoStmt->execute(); 
        } catch (PDOException  $e) {  
            exit('SQL语句：'.$sql.'<br />错误信息：'.$e->getMessage());  
        }  
    }  


   /*-------------------------------------------------------------------------------------
   | 是否为数据库更改操作 
   |-------------------------------------------------------------------------------------- 
   | @param string $query SQL指令 
   | @return boolen 如果是查询操作返回false
   -------------------------------------------------------------------------------------*/ 
   private function isMainIps($query) 
   { 
	$queryIps = 'INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|LOAD DATA|SELECT .* INTO|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK'; 

	if (preg_match('/^\s*"?(' . $queryIps . ')\s+/i', $query)) { 
		return true; 
	} 

	return false; 
   } 


    /*-------------------------------------------------------------------------------------
    | 启动事务 
    -------------------------------------------------------------------------------------*/ 
    public function beginTransaction() 
    { 
	if (!$this->_pdo) return false; 

	if (!$this->_pdo->inTransaction()) { 
		$this->_pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE); 
		$this->_pdo->beginTransaction(); 
	}  
	return; 
    } 


    /*-------------------------------------------------------------------------------------
    | 用于非自动提交状态下面的查询提交 
    |--------------------------------------------------------------------------------------
    | @return boolen 
    -------------------------------------------------------------------------------------*/ 
    public function commit() 
    { 
	if (!$this->_pdo) return false;

	if ($this->_pdo->inTransaction()) { 
		$result = $this->_pdo->commit(); 
		$this->_pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, TRUE); 
		if(!$result){ 
			throw new Exception('事务自动提交失败！'); 
			return false; 
		} 
	} 
	return true; 
    } 


    /*------------------------------------------------------------------------------------
    | 事务回滚 
    |------------------------------------------------------------------------------------- 
    | @return boolen 
    ------------------------------------------------------------------------------------*/ 
    public function rollback() 
    { 
	if (!$this->_pdo) return false; 
	
	if ($this->_pdo->inTransaction()) { 
		$result = $this->_pdo->rollback();
		$this->_pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, TRUE); 
		if(!$result){ 
			throw new Exception('事务自动回滚失败！'); 
			return false; 
		} 
	} 
	return true; 
    } 

    
} 
?> 
