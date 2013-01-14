<?php

ini_set('display_errors', '1');
require_once('assets/scriptrepo.class.php');
require_once('assets/bcrypt.php');
require_once('assets/logger.class.php');
require_once('password.php');

class ScriptRepoAPI extends ScriptRepo {

    public $data = array();

    public function __construct() {
        $this->populateVariables();
        $this->handleArgs($_GET);
        $this->outputData();
    }

    public function handleArgs($args) {
        switch (true) {
            case isset($args['login']) && isset($args['user']) && isset($args['pass']):
                $loginStatus = $this->loginUser($args['user'], $args['pass']);
                if ($loginStatus['loginSuccess']) {
                    $sessionKey = $this->makeSessionID();
                    $user = $this->databaseHandle->real_escape_string($args['user']);
                    $time = time() + 3600;
                    $this->queryDatabase("INSERT INTO repo_sessions (id, username, sessionKey, time) VALUES ('NULL', '$user', '$sessionKey', '$time')");
                    $this->data = array_merge($loginStatus, array('success' => true, 'sessionKey' => $sessionKey));
                } else {
                    $this->data = array_merge($loginStatus, array('success' => true));
                }
                break;
            case isset($args['logout']) && isset($args['sessionKey']):
                $sessionKey = $this->databaseHandle->real_escape_string($args['sessionKey']);
                if ($this->validateSessionID($sessionKey)) {
                    $this->queryDatabase("DELETE FROM repo_sessions WHERE sessionKey='$sessionKey'");
                    $this->data = array('success' => true);
                } else {
                    $this->data = array('success' => false);
                }
                break;
            case isset($args['browse']):
                if (isset($args['count'])) {
                    $count = intval($args['count']);
                } else {
                    $count = 20;
                }
                if (isset($args['page'])) {
                    $page = intval($args['page']);
                } else {
                    $page = 1;
                }
                $queryBrowse = $this->queryDatabase("SELECT * FROM repo_entries WHERE privacy=1");
                $this->data = array_merge(array('success' => true), $this->getResults($queryBrowse, $count, $page));
                break;
            case isset($args['view']) && isset($args['pubID']):
                $pubID = $this->databaseHandle->real_escape_string($args['pubID']);
                $queryView = $this->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                if ($queryView->num_rows != 1) {
                    $this->data = array('success' => false);
                } else {
                    $rowView = $queryView->fetch_assoc();
                    $this->queryDatabase("UPDATE repo_entries SET views='" . ($rowView['views'] + 1) . "' WHERE pubID='$pubID'");
                    $rowView['views'] = $rowView['views'] + 1;
                    $queryCode = $this->queryDatabase("SELECT * FROM repo_code WHERE pubID='$pubID'");
                    $this->data = array_merge(array('success' => true), array('entryData' => $rowView, 'codeData' => $queryCode->fetch_assoc()));
                }
                break;
            case isset($args['like']) && isset($args['sessionKey']) && isset($args['pubID']):
                $pubID = $this->databaseHandle->real_escape_string($args['pubID']);
                $queryEntry = $this->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                $userInfo = $this->validateSessionID($args['sessionKey']);
                if ($queryEntry->num_rows == 1 && $userInfo) {
                    $queryLike = $this->queryDatabase("SELECT * FROM repo_likes WHERE author='" . $userInfo['username'] . "'");
                    if ($queryLike->num_rows == 0) {
                        // Hasn't been liked yet. Go ahead and like it.
                        $this->queryDatabase("INSERT INTO repo_likes");
                    } else {
                        $this->data = array('success' => false);
                    }
                } else {
                    $this->data = array('success' => false);
                }
                break;
            case isset($args['post']) && isset($args['sessionKey']):
                $sessionKey = $this->databaseHandle->real_escape_string($args['sessionKey']);
                $userInfo = $this->validateSessionID($args['sessionKey']);
                if ($userInfo) {
                    $this->username = $userInfo['username'];
                    if (isset($_POST['SubmitScript'])) {
                        $success = $this->postScript($_POST);
                        $this->data = array_merge($success, array('success' => true));
                    } else {
                        $this->data = array('success' => false);
                    }
                } else {
                    $this->data = array('success' => false);
                }
                break;
            case isset($args['search']):
                if (isset($args['count'])) {
                    $count = intval($args['count']);
                } else {
                    $count = 20;
                }
                if (isset($args['page'])) {
                    $page = intval($args['page']);
                } else {
                    $page = 1;
                }
                if (isset($args['query'])) {
                    $queryTerm = $this->databaseHandle->real_escape_string($args['query']);
                    $querySearch = $this->queryDatabase("SELECT * FROM repo_entries WHERE MATCH (name,author,description,tags) AGAINST('$queryTerm' IN BOOLEAN MODE)");
                    $this->data = array_merge(array('success' => true), array("results" => $this->getResults($querySearch, $count, $page)));
                } else {
                    $this->data = array('success' => false);
                }
                break;
            case isset($args['download']) && isset($args['pubID']):
                $pubID = $this->databaseHandle->real_escape_string($args['pubID']);
                $queryExist = $this->queryDatabase("SELECT * FROM repo_code WHERE pubID='$pubID'");
                if ($queryExist->num_rows == 1) {
                    $existRow = $queryExist->fetch_assoc();
                    $queryData = $this->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                    $queryData = $queryData->fetch_assoc();
                    $this->data = array('success' => true, 'code' => $existRow['code'], 'name' => $queryData['name']);
                } else {
                    $this->data = array('success' => false);
                }
                break;
            default:
                $this->data = array('success' => false);
        }
    }

    public function outputData() {
        echo json_encode($this->data);
        exit;
    }

    private function makeSessionID() {
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

    private function validateSessionID($sessionKey) {
        $sessionKey = $this->databaseHandle->real_escape_string($sessionKey);
        $query = $this->queryDatabase("SELECT * FROM repo_sessions WHERE sessionKey='$sessionKey'");
        $row = $query->fetch_assoc();
        if ($query->num_rows != 1) {
            return false;
        } elseif ($row['time'] < time()) {
            return false;
        } else {
            return $row;
        }
    }

}

new ScriptRepoAPI();
?>