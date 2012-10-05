<?php
class Logger {
    private $accessHandle;
    private $errorHandle;
    private $accessLog="/home/agentkid/logs/access.log";
    private $errorLog="/home/agentkid/logs/error.log";
    public function __construct(){
        $this->errorHandle = fopen($this->errorLog, "a");
        $this->accessHandle = fopen($this->accessLog, "a");
    }
    public function __destruct(){
        fclose($this->errorHandle);
        fclose($this->accessHandle);
    }
    public function accessLog($message){
        fwrite($this->accessHandle, $this->getPrefix().$message);
    }
    public function errorLog($message){
        fwrite($this->errorHandle, $this->getPrefix().$message);
    }
    private function getPrefix(){
        return "[".date("Y-m-d H:i:s")."] ".$_SERVER['REMOTE_ADDR']." - ";
    }
}

?>
