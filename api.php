<?php
session_start();
var_dump($_SESSION);
ini_set('display_errors', '1');
require_once('assets/scriptrepo.class.php');
require_once('assets/bcrypt.php');
require_once('password.php');
class ScriptRepoAPI{
    public $data = array();
    protected $ScriptRepo;
    public function __construct($args){
        $this->ScriptRepo = new ScriptRepo();
        $this->handleArgs($args);
        $this->outputData();
    }
    public function handleArgs($args){
        switch(true){
            case count($args)==0:
                $this->data = array('success' => false);
                break;
            case isset($args['login']):
                if(isset($args['user']) && isset($args['pass'])){
                    $loginStatus = $this->ScriptRepo->loginUser($args['user'], $args['pass']);
                    if($loginStatus['loginSuccess']){
                        $this->data = array_merge($loginStatus, array('success' => true, 'sessionKey' => NULL));
                    }else{
                        $this->data = array_merge($loginStatus, array('success' => true));
                    }
                }else{
                    $this->data = array('success' => false);
                }
                break;
        }
    }
    public function outputData(){
        echo json_encode($this->data);
        exit;
    }
}
new ScriptRepoAPI($_GET);
?>