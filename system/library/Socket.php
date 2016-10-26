<?php
/**
 * Socket class
 */
Class Socket
{
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
    
    function __construct()
    {
        
    }
    /**
     * Singleton pattern. Returns the same instance to all callers
     *
     * @return Socket
     */
    public static function singleton()
    {
        if (self::$instance == null || ! self::$instance instanceof Socket)
        {
            self::$instance = new Socket();
           
        }
        return self::$instance;
    }
    /**
     * Connects to the socket with the given address and port
     * 
     * @return void
     */
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
    
    /**
     * Disconnects from the server
     * 
     * @return True on succes, false if the connection was already closed
     */
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
    /**
     * Sends a command to the server
     * 
     * @return string Server response
     */
    public function sendRequest($command)
    {
        if($this->validateConnection())
        {
            $result = socket_write($this->connection,$command,strlen($command));
            return $result;
        }
        $this->_throwError("Sending command \"{$command}\" failed.<br>Reason: Not connected");
    }
    
    
    
    public function isConn()
    {
        return $this->connectionState;
    }
    
    
    public function getUnreadBytes()
    {
        
        $info = socket_get_status($this->connection);
        return $info['unread_bytes'];

    }

    
    public function getConnName(&$addr, &$port)
    {
        if ($this->validateConnection())
        {
            socket_getsockname($this->connection,$addr,$port);
        }
    }
    
   
    
    /**
     * Gets the server response (not multilined)
     * 
     * @return string Server response
     */
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


    public function waitForResponse()
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


    /**
     * Validates the connection state
     * 
     * @return bool
     */
    private function validateConnection()
    {
        return (is_resource($this->connection) && ($this->connectionState != self::DISCONNECTED));
    }


    /**
     * Throws an error
     * 
     * @return void
     */
    private function _throwError($errorMessage)
    {
        echo "Socket error: " . $errorMessage;
    }
    /**
     * Throws an message
     * 
     * @return void
     */
    private function _throwMsg($msg)
    {
        if ($this->debug)
        {
            echo "Socket message: " . $msg . "\n\n";
        }
    }
    /**
     * If there still was a connection alive, disconnect it
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}

?>
