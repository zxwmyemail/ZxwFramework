app_log下面主要用于生成程序日志文件，使用/system/library/Log.php类，可自在app_log文件夹下生成日志！

使用方法：
    $logIns = Log::getInstance();
    $logIns->logMessage("test",Log::INFO,'myTest'); 
