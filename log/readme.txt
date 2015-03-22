说明文件，log下面主要用于生成日志文件，使/system/library/Log.php类，可自在log文件夹下生成日志！

使用方法：
    $logIns = Log::getInstance();
    $logIns->logMessage("test",Log::INFO,'myTest'); 
