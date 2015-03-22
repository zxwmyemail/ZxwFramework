<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************
 * 数据库工厂
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

class DBFactory {

    private $_MysqlInstance = null;
    private $_MysqlPDOInstance = null;
    private $_oracleInstance = null;
    private $_oraclePDOInstance = null;
    private $_DBConfig   = null;

    function __construct($DBConfig, $whichDB='default') 
    {
        $this->_DBConfig = $DBConfig[$whichDB];
    }

    public function __get($dbName='mysql') 
    {
        switch (strtolower($dbName)) {      	
            case 'mysql' :
                if (!isset( $this->_MysqlInstance ))
                {
                	$mysql = new MySQL();

                	$mysql->init(
                		$this->_DBConfig['db_host'],
                		$this->_DBConfig['db_user'],
                		$this->_DBConfig['db_password'],
                		$this->_DBConfig['db_database'],
                		$this->_DBConfig['db_charset'],
                		$this->_DBConfig['db_conn']
                	);

                	$this->_MysqlInstance = $mysql;

                    return $this->_MysqlInstance;
                }
                break;
            case 'mysqlPDO' :
                if (!isset( $this->_MysqlPDOInstance ))
                {   
                    $DB_DNS = 'mysql:host='.$this->_DBConfig['db_host'].';port='.$this->_DBConfig['db_port'].';dbname='.$this->_DBConfig['db_database'];
                    $pdoConfig = array( 
                        'username'  => $this->_DBConfig['db_user'], 
                        'password'  => $this->_DBConfig['db_password'],
                        'dbcharset' => $this->_DBConfig['db_charset'],
                        'pconnect'  => $this->_DBConfig['db_conn'],   //是否永久链接，0非永久，1永久
                        'dns'       => $DB_DNS
                    );

                    $this->_MysqlPDOInstance = BasePDO::getInstance($pdoConfig);

                    return $this->_MysqlPDOInstance;
                }
                break;
            case 'oracle' :
                if (!isset( $this->_oracleInstance ))
                {
                    $oracle = new Oracle();

                    $oracle->init(
                        $this->_DBConfig['db_host'],
                        $this->_DBConfig['db_port'],
                        $this->_DBConfig['db_server_name'],
                        $this->_DBConfig['db_user'],
                        $this->_DBConfig['db_password']
                    );

                    $this->_oracleInstance = $oracle;

                    return $this->_oracleInstance;
                }
                break;
            case 'oraclePDO' :
                if (!isset( $this->_oraclePDOInstance ))
                {   
                    $tns = "(DESCRIPTION =
                                (ADDRESS_LIST =
                                  (ADDRESS = (PROTOCOL = TCP)(HOST = ".$this->_DBConfig['db_host'].")(PORT = ".$this->_DBConfig['db_port']."))
                                )
                                (CONNECT_DATA =
                                  (SERVICE_NAME = ".$this->_DBConfig['db_server_name'].")
                                )
                            )";

                    $DB_DNS = "oci:dbname=" . $tns;
                    $pdoConfig = array( 
                        'username'  => $this->_DBConfig['db_user'], 
                        'password'  => $this->_DBConfig['db_password'],
                        'dbcharset' => $this->_DBConfig['db_charset'],
                        'pconnect'  => $this->_DBConfig['db_conn'],   //是否永久链接，0非永久，1永久
                        'dns'       => $DB_DNS
                    );

                    $this->_oraclePDOInstance = BasePDO::getInstance($pdoConfig);

                    return $this->_oraclePDOInstance;
                }
                break;
            default :
                # code
                break;
        }
    }
}
?>
