<?php
if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');
/*********************************************************************************************
 * 数据库工厂
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

class DBFactory {

    private $_DBConfig = null;
    private $_whichDB  = null;

    function __construct($DBConfig, $whichDB='master') 
    {
        $this->_DBConfig = $DBConfig[$whichDB];
        $this->_whichDB  = $whichDB;
    }

    public function __get($dbName='mysql') 
    {
        switch ($dbName) {      	
            case 'mysql' :
            	$mysql = new MySQL();

            	$mysql->init(
            		$this->_DBConfig['db_host'],
            		$this->_DBConfig['db_user'],
            		$this->_DBConfig['db_password'],
            		$this->_DBConfig['db_database'],
            		$this->_DBConfig['db_charset'],
            		$this->_DBConfig['db_conn']
            	);

            	return $mysql;
                break;
            case 'mysqlPDO' :
                $DB_DNS = 'mysql:host='.$this->_DBConfig['db_host'].';port='.$this->_DBConfig['db_port'].';dbname='.$this->_DBConfig['db_database'];
                $pdoConfig = array( 
                    'username'  => $this->_DBConfig['db_user'], 
                    'password'  => $this->_DBConfig['db_password'],
                    'dbcharset' => $this->_DBConfig['db_charset'],
                    'pconnect'  => $this->_DBConfig['db_conn'],   //是否永久链接，0非永久，1永久
                    'dns'       => $DB_DNS
                );

                return BasePDO::getInstance($pdoConfig, $this->_whichDB);
                break;
            case 'oracle' :
                $oracle = new Oracle();

                $oracle->init(
                    $this->_DBConfig['db_host'],
                    $this->_DBConfig['db_port'],
                    $this->_DBConfig['db_server_name'],
                    $this->_DBConfig['db_user'],
                    $this->_DBConfig['db_password']
                );

                return $oracle;
                break;
            case 'oraclePDO' :
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

                return BasePDO::getInstance($pdoConfig, $this->_whichDB);
                break;
            default :
                # code
                break;
        }
    }
}
?>
