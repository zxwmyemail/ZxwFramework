<?php
namespace core\library;
/***********************************************************************************************
 简单的tcp套接字类
 @author    iProg
 @version   1.0
 @date      2015-05-28
************************************************************************************************/

Class Socket {
    const CONNECTED = true;
    const DISCONNECTED = false;
    const BUFFER_SIZE = 10240;
    
    private static $instance;
    private $connection = null;
    private $connectionState = false;
    
    private $defaultHost = "127.0.0.1";
    private $defaultPort = 10101;
    private $defaultTimeout = 3;
    
    public  $debug = false;
    
    function __construct(){}
    
    /*---------------------------------------------------------------------------------------------
    | 采用单例模式
    ---------------------------------------------------------------------------------------------*/
    public static function singleton()
    {
        if (self::$instance == null || ! self::$instance instanceof Socket)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /*---------------------------------------------------------------------------------------------
    | 连接tcp服务器，参数依次为ip地址或域名、端口、超时时间
    ---------------------------------------------------------------------------------------------*/
    public function connect($serverHost=false, $serverPort=false, $timeOut=false)
    {        
        $serverHost = ($serverHost == false) ? $this->defaultHost : $serverHost;
        $serverPort = ($serverPort == false) ? $this->defaultPort : $serverPort;
        $timeOut = ($timeOut == false) ? $this->defaultTimeout : $timeOut;
        
        $this->connection = socket_create(AF_INET,SOCK_STREAM,SOL_TCP); 
        socket_set_nonblock($this->connection) or die(0);
        $time = time();
        while (socket_connect($this->connection,$serverHost,$serverPort) == false)    //如果没有连接上就一直死循环
        {
            if ((time() - $time) >= $timeOut)    //每次都需要去判断一下是否超时了
            {
                socket_close($this->connection);
                return false;
            } else {
                sleep(1);
                continue; 
            }
        }

        socket_set_option($this->connection, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$timeOut, "usec"=>0));
        $this->connectionState = self::CONNECTED;
        return true;
    }
    
    /*---------------------------------------------------------------------------------------------
    | 断开与tcp服务器的连接
    ---------------------------------------------------------------------------------------------*/
    public function disconnect()
    {
        if($this->validateConnection())
        {
            socket_close($this->connection);
            $this->connectionState = self::DISCONNECTED;
            $this->_throwMsg("Socket disconnected!");
            return true;
        }
        return false;
    }
    
    /*---------------------------------------------------------------------------------------------
    | 向tcp服务器发送数据
    ---------------------------------------------------------------------------------------------*/
    public function sendRequest($command)
    {
        if($this->validateConnection())
        {
            $result = socket_write($this->connection,$command,strlen($command));
            return $result;
        }
        $this->_throwError("Sending command \"{$command}\" failed.<br>Reason: Not connected");
    }
    
    /*---------------------------------------------------------------------------------------------
    | 返回未读的字节数
    ---------------------------------------------------------------------------------------------*/
    public function getUnreadBytes()
    {
        $info = socket_get_status($this->connection);
        return $info['unread_bytes'];
    }

    /*---------------------------------------------------------------------------------------------
    | 获取连接的套接字的名字
    ---------------------------------------------------------------------------------------------*/
    public function getConnName(&$addr, &$port)
    {
        if ($this->validateConnection())
        {
            socket_getsockname($this->connection,$addr,$port);
        }
    }
    
   
    /*---------------------------------------------------------------------------------------------
    | 获取tcp服务器返回的字节流数据
    ---------------------------------------------------------------------------------------------*/
    public function getResponse($totalBytes)
    {
        $byteStr = '';
        $haveReadBytes = 0;
        while ($haveReadBytes < $totalBytes) {
            $ret = socket_read($this->connection, self::BUFFER_SIZE, PHP_BINARY_READ); 
            $byteStr .= $ret;
            $haveReadBytes += strlen($ret);
        }     
        return $byteStr;
    }

    /*---------------------------------------------------------------------------------------------
    | 在连接有效的情况下，获取tcp服务器返回的字节流数据
    ---------------------------------------------------------------------------------------------*/
    public function waitForResponse($totalBytes)
    {
        if($this->validateConnection())
        {
            $byteStr = '';
            $haveReadBytes = 0;
            while ($haveReadBytes < $totalBytes) {
                $ret = socket_read($this->connection, self::BUFFER_SIZE, PHP_BINARY_READ); 
                $byteStr .= $ret;
                $haveReadBytes += strlen($ret);
            }     
            return $byteStr;
        }
        
        $this->_throwError("Receiving response from server failed.<br>Reason: Not connected");
        return false;
    }

    /*---------------------------------------------------------------------------------------------
    | 返回是否成功连接，校验连接的有效性
    ---------------------------------------------------------------------------------------------*/
    private function validateConnection()
    {
        return (is_resource($this->connection) && ($this->connectionState != self::DISCONNECTED));
    }

    /*---------------------------------------------------------------------------------------------
    | 一个简单额异常抛出函数
    ---------------------------------------------------------------------------------------------*/
    private function _throwError($errorMessage)
    {
        echo "Socket error: " . $errorMessage;
    }
    
    /*---------------------------------------------------------------------------------------------
    | 在调试模式下，一个简单的信息打印函数
    ---------------------------------------------------------------------------------------------*/
    private function _throwMsg($msg)
    {
        if ($this->debug)
        {
            echo "Socket message: " . $msg . "\n\n";
        }
    }
    
    /*---------------------------------------------------------------------------------------------
    | 析构函数，断开连接
    ---------------------------------------------------------------------------------------------*/
    public function __destruct()
    {
        $this->disconnect();
    }
}

?>
