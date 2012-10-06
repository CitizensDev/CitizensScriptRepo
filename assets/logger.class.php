<?php
class Logger {
    private $accessHandle;
    private $errorHandle;
    private $accessLog="/var/log/scriptrepo/access.log";
    private $errorLog="/var/log/scriptrepo/error.log";
    public $username;
    public function __construct(){
        $this->errorHandle = fopen($this->errorLog, "a");
        $this->accessHandle = fopen($this->accessLog, "a");
    }
    public function __destruct(){
        fclose($this->errorHandle);
        fclose($this->accessHandle);
    }
    public function accessLog($message){
        if(isset($this->username)){ $append = "$this->username - "; }else{ $append=null; }
        fwrite($this->accessHandle, $this->getPrefix().$append.$message."\n");
    }
    public function errorLog($message){
        if(isset($this->username)){ $append = "$this->username - "; }else{ $append=null; }
        fwrite($this->errorHandle, $this->getPrefix().$append.$message."\n");
    }
    private function getPrefix(){
        return "[".date("Y-m-d H:i:s")."] ".$_SERVER['REMOTE_ADDR']." - ";
    }
}

?>