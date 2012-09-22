<?php
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
                        $sessionKey = $this->makeSessionID();
                        $user = $this->ScriptRepo->databaseHandle->real_escape_string($args['user']);
                        $time = time()+3600;
                        $this->ScriptRepo->queryDatabase("INSERT INTO repo_sessions (id, username, sessionKey, time) VALUES ('NULL', '$user', '$sessionKey', '$time')");
                        $this->data = array_merge($loginStatus, array('success' => true, 'sessionKey' => $sessionKey));
                    }else{
                        $this->data = array_merge($loginStatus, array('success' => true));
                    }
                }else{
                    $this->data = array('success' => false);
                }
                break;
            case isset($args['logout']):
                if(isset($args['sessionKey'])){
                    $sessionKey = $this->ScriptRepo->databaseHandle->real_escape_string($args['sessionKey']);
                    if($this->validateSessionID($sessionKey)){
                        $this->ScriptRepo->queryDatabase("DELETE FROM repo_sessions WHERE sessionKey='$sessionKey'");
                        $this->data = array('success' => true);
                    }else{
                        $this->data = array('success' => false);
                    }
                }else{
                    $this->data = array('success' => false);
                }
                break;
            case isset($args['browse']):
                if(isset($args['count'])){ $count = intval($args['count']); }else{ $count = 20; }
                if(isset($args['page'])){ $page = intval($args['page']); }else{ $page = 1; }
                $queryBrowse = $this->ScriptRepo->queryDatabase("SELECT * FROM repo_entries WHERE privacy=1");
                $this->data = array_merge(array('success' => true), $this->ScriptRepo->getResults($queryBrowse, $count, $page));
                break;
            case isset($args['view']):
                if(isset($args['pubID'])){
                    $pubID = $this->ScriptRepo->databaseHandle->real_escape_string($args['pubID']);
                    $queryView = $this->ScriptRepo->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                    if($queryView->num_rows!=1){
                        $this->data = array('success' => false);
                    }else{
                        $queryCode = $this->ScriptRepo->queryDatabase("SELECT * FROM repo_code WHERE pubID='$pubID'");
                        $this->data = array_merge(array('success' => true), array('entryData' => $queryView->fetch_assoc(), 'codeData' => $queryCode->fetch_assoc()));
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
    private function makeSessionID(){
        $character_set_array = array();
        $character_set_array[] = array('count' => 8, 'characters' => 'abcdefghijklmnopqrstuvwxyz');
        $character_set_array[] = array('count' => 8, 'characters' => '0123456789');
        $temp_array = array();
        foreach ($character_set_array as $character_set) {
            for ($i = 0; $i < $character_set['count']; $i++) {
                $temp_array[] = $character_set['characters'][rand(0, strlen($character_set['characters']) - 1)];
            }
        }
        shuffle($temp_array);
        return implode('', $temp_array);
    }
    private function validateSessionID($sessionKey){
        $sessionKey = $this->ScriptRepo->databaseHandle->real_escape_string($sessionKey);
        $query = $this->ScriptRepo->queryDatabase("SELECT * FROM repo_sessions WHERE sessionKey='$sessionKey'");
        $row = $query->fetch_assoc();
        if($query->num_rows!=1){
            return false;
        }elseif($row['time']<time()){
            return false;
        }else{
            return true;
        }
    }
}
new ScriptRepoAPI($_GET);
?>