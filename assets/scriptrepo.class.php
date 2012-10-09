<?php
class ScriptRepo{
    public $mainSite = 'http://scripts.citizensnpcs.com/';
    public $rootDir = '/usr/share/nginx/www/scripts/';
    public $loggedIn = false;
    public $admin = false;
    public $bCrypt;
    public $username;
    public $databaseHandle;
    public $ayah;
    public $logHandle;
    public $maintenenceMode=false; // Maintenence mode.
    public $llMaintenence=false; // Low level (no DB and therefore no login checks) maintenence mode.
    protected $smarty;
    public function __construct(){
        $this->initSmarty();
        $this->populateVariables();
        $this->webStuff();
        $_SERVER['REQUEST_URI_PATH'] = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
        $path = explode('/', trim($_SERVER['REQUEST_URI_PATH'], '/'));
        array_shift($_GET);
        if($this->llMaintenence){
            die("We're sorry, but the site is in maintenence right now. Please try again later.");
        }
        $this->handlePage($path);
        
    }
    public function populateVariables(){
        require('password.php');
        $this->databaseHandle = new mysqli('localhost', 'repo', $password, 'ScriptRepo');
        $this->bCrypt = new Bcrypt(12);
        require_once('pages.class.php');
        $this->pageHandle = new Pages();
        $this->logHandle = new Logger();
        function handleError($errno, $errstr, $errfile, $errline){
            $logHandle = new Logger();
            $logHandle->errorLog("Type: ".$errno.", Error: ".$errstr.", File:".$errfile.", Line: ".$errline);
            return true;
        }
        set_error_handler('handleError');
    }
    public function initSmarty(){
        $this->smarty = new Smarty;
        $this->smarty->setTemplateDir($this->rootDir.'assets/templates');
        $this->smarty->setCompileDir($this->rootDir.'assets/Smarty/templates_c');
        $this->smarty->setCacheDir($this->rootDir.'assets/Smarty/cache');
        $this->smarty->setConfigDir($this->rootDir.'assets/Smarty/configs');
        $this->smarty->assign('ScriptRepo', $this);
    }
    public function webStuff(){
        if(isset($_SESSION['loggedIn'])){ $this->loggedIn = $_SESSION['loggedIn']; }else{
            $_SESSION['loggedIn'] = false;
            $this->loggedIn = false;
        }
        if(isset($_SESSION['admin'])){ $this->admin = $_SESSION['admin']; }else{
            $_SESSION['admin'] = false;
            $this->admin = false;
        }
        if($this->loggedIn){ $this->username = $_SESSION['username']; }
        if($this->admin){
            $query = $this->queryDatabase("SELECT * FROM repo_flags");
            if($query->num_rows>0){ $this->smarty->assign('adminNeeded', true); }else{ $this->smarty->assign('adminNeeded', false); }
        }
        if(isset($_POST['q'])){
            $query = str_replace(array("%20", " "), "+", $_POST['q']);
            $this->redirect('search/'.$query);
        }
        if(isset($_POST['q2'])){
            $query = str_replace(array("%20", " "), "+", $_POST['searchBox']);
            $this->redirect('search/'.$query);
        }
    }
    public function handlePage($path){
        $variables = $this->getVariables($path);
        $this->smarty->assign($variables);
        $this->smarty->display('index.tpl');
    }
    public function getVariables($path){
        $this->pageHandle->mainClass = $this;
        $this->pageHandle->logHandle = $this->logHandle;
        if($this->loggedIn){ $this->logHandle->username = $this->username; }
        $this->pageHandle->path = $path;
        /*if($this->maintenenceMode){
            if(!$this->loggedIn){
                $_SESSION['loginError'] = 'The site is in maintenence mode. If you are staff, please log in.';
                $this->redirect('login');
            }
        } */ // Future maintenence mode stuff
        switch($path[0]){
            case 'credits':
                $this->pageHandle->credits();
                break;
            case 'download':
                $this->pageHandle->download();
                break;
            case 'raw':
                $this->pageHandle->raw();
                break;
            case 'login':
                $this->pageHandle->login();
                break;
            case 'settings':
                $this->pageHandle->settings();
                break;
            case 'logout':
                $this->pageHandle->logout();
                break;
            case 'resendconfirmation':
                $this->pageHandle->resendconfirmation();
                break;
            case 'register':
                $this->pageHandle->register();
                break;
            case 'post':
                $this->pageHandle->post();
                break;
            case 'test':
                $this->pageHandle->test();
                break;
            case 'verify':
                $this->pageHandle->verify();
                break;
            case 'edit':
                $this->pageHandle->edit();
                break;
            case 'myscripts':
                $this->pageHandle->myscripts();
                break;
            case 'search':
                $this->pageHandle->search();
                break;
            case 'admin':
                $this->pageHandle->admin();
                break;
            case 'support':
                $this->pageHandle->support();
                break;
            case 'browse':
                $this->pageHandle->browse();
                break;
            case 'view':
                $this->pageHandle->view();
                break;
            case 'user':
                $this->pageHandle->user();
                break;
            case 'index':
            case '':
            case 'home':
                $this->pageHandle->home();
                break;
            case 'action':
                $this->pageHandle->action();
                break;
            default:
                $this->pageHandle->page404();
                break;
        }
        return $this->pageHandle->variableArray;
    }
    public function loginUser($username, $password){
        $username = $this->databaseHandle->real_escape_string($username);
        if($username=="" || $password==""){
                return array(
                    'loginSuccess' => false,
                    'username' => $username,
                    'loginError' => 'You must enter both a username and password.',
                    'userError' => true,
                    'passwordError' => true
                );
            }elseif(!$this->isValidLogin($username, $password)){
                return array(
                    'loginSuccess' => false,
                    'username' => $username,
                    'loginError' => 'Invalid username or password!',
                    'passwordError' => true
                );
            }elseif(!$this->isActiveUser($username)){
                $_SESSION['attemptedUsername'] = $username;
                return array(
                    'loginSuccess' => false,
                    'username' => $username,
                    'loginError' => 'You must activate your email before you can log in! <a href="http://scripts.citizensnpcs.com/resendConfirmation">Resend confirmation email.</a>',
                );
            }else{
                // Login
                $query = $this->queryDatabase("SELECT * FROM repo_users WHERE username='".$username."'");
                if($query!==false){
                    $row = $query->fetch_assoc();
                    if($row['staff']==1){
                        $_SESSION['admin'] = true;
                        $this->admin = true;
                    }else{
                        $_SESSION['admin'] = false;
                        $this->admin = false;
                    }
                }
                return array( 'loginSuccess' => true );
            }
    }
    public function registerUser($postData){
        $email = $this->databaseHandle->real_escape_string($_POST['email']);
        $emailQuery = $this->queryDatabase("SELECT * FROM repo_users WHERE email='$email'");
        $user = $this->databaseHandle->real_escape_string($_POST['username']);
        $userQuery = $this->queryDatabase("SELECT * FROM repo_users WHERE user='$user'");
        if(strlen($postData['password'])<5){
            return array(
                'registerSuccess' => false,
                'registerError' => 'Password must be more than 5 characters!',
                'username' => $user,
                'email' => $email,
                'passwordError' => true
            );
        }elseif($postData['password']!==$postData['passwordConfirm']){
            return array(
                'registerSuccess' => false,
                'registerError' => 'Passwords do not match!',
                'username' => $user,
                'email' => $email,
                'passwordError' => true
            );
        }elseif(strlen($postData['username'])<3 || strlen($postData['username']>17)){
            return array(
                'registerSuccess' => false,
                'registerError' => 'Username must be between 4 and 16 characters!',
                'username' => $user,
                'email' => $email,
                'usernameError' => true
            );
        }elseif(!filter_var($postData['email'], FILTER_VALIDATE_EMAIL)){
            return array(
                'registerSuccess' => false,
                'username' => $user,
                'email' => $email,
                'emailError' => true,
                'registerError' => 'Invalid email address!'
            );
        }elseif($emailQuery->num_rows>0){
            return array(
                'registerSuccess' => false,
                'username' => $user,
                'email' => $email,
                'emailError' => true,
                'registerError' => 'Email already in use!'
            );
        }elseif($userQuery->num_rows===0){
            return array(
                'registerSuccess' => false,
                'username' => $user,
                'email' => $email,
                'userError' => true,
                'registerError' => 'Username already in use!'
            );
        }elseif(!$this->ayah->scoreResult()){
            return array(
                'registerSuccess' => false,
                'username' => $user,
                'email' => $email,
                'ayahError' => true,
                'registerError' => 'The AreYouAHuman game wasn\'t completed properly. Please try it again.',
            );
        }else{
            $pass = $this->bCrypt->hash($postData['password']);
            $this->queryDatabase("INSERT INTO repo_users (id, username, password, email, status, staff) VALUES ('NULL', '$user', '$pass', '$email', '0', false)");
            $mailer = new Mailer();
            $mailer->sendConfirmationEmail($email, $user);
            return array( 'registerSuccess' => true );
        }
    }
    public function postScript($postData, $oldID=false){
        $tagsRaw = explode(',', $postData['tags']);
        $tags = array();
        foreach($tagsRaw as $tag){
            if($tag!=""){ array_push($tags, str_replace("_", "", trim($tag))); }
        }
        $values = array();
        if(isset($postData['name']) && $postData['name']!=""){ $values = array_merge($values, array('name' => $postData['name'])); }
        if(isset($postData['scriptCode']) && $postData['scriptCode']!=""){ $values = array_merge($values, array('scriptCode' => $postData['scriptCode'])); }
        if(isset($postData['Description']) && $postData['Description']!=""){ $values = array_merge($values, array('description' => $postData['Description'])); }
        if(isset($postData['tags']) && $postData['tags']!=""){ $values = array_merge($values, array('tags' => $postData['tags'])); }
        if($postData['name']==""){
            return array_merge($values, array(
                'postSuccess' => false,
                'postError' => 'Name must not be empty!',
                'nameError' => true
            ));
        }elseif(strlen($postData['name'])>50){
            return array_merge($values, array(
                'postSuccess' => false,
                'postError' => 'Name must be less than 50 characters!',
                'nameError' => true
            ));
        }elseif($postData['Description']==""){
            return array_merge($values, array(
                'postSuccess' => false,
                'postError' => 'Description must not be empty!',
                'descriptionError' => true
            ));
        }elseif($postData['scriptCode']==""){
            return array_merge($values, array(
                'postSuccess' => false,
                'postError' => 'Code must not be empty!',
                'scriptError' => true
            ));
        }elseif($postData['typeOfScript']=="None"){
            return array_merge($values, array(
                'postSuccess' => false,
                'postError' => 'Script type must be selected!',
                'typeError' => true,
            ));
        }elseif(count($tags)==0){
            return array_merge($values, array(
                'postSuccess' => false,
                'postError' => 'You must enter at least one tag!',
                'tagError' => true
            ));
        }else{
            $typeOfScript = intval($postData['typeOfScript']);
            if(isset($postData['privacy'])){ $privacy = 2; }else{ $privacy = 1; }
            $scriptCode = $this->databaseHandle->real_escape_string($postData['scriptCode']);
            $unsafeDescription = str_replace("<script>", htmlspecialchars("<script>"), strtolower($postData['Description']));
            $description = $this->databaseHandle->real_escape_string($unsafeDescription);
            $name = $this->databaseHandle->real_escape_string($postData['name']);
            $username = $this->username;
            $tagString = implode(', ', $tags);
            $timestamp = time();
            if(!$oldID){
                $pubID = $this->generatePublicID();
                $this->queryDatabase("INSERT INTO repo_entries (id, pubID, author, name, description, tags, privacy, scriptType, timestamp, edited, downloads, views) VALUES ('NULL', '$pubID', '$username', '$name', '$description', '$tagString', '$privacy', '$typeOfScript', '$timestamp', $timestamp, 0, 0)");
                $this->queryDatabase("INSERT INTO repo_code (id, pubID, code) VALUES ('NULL', '$pubID', '$scriptCode')");
            }else{
                $pubID = $oldID;
                $this->queryDatabase("UPDATE repo_entries SET name='$name', description='$description', tags='$tagString', privacy='$privacy', scriptType='$typeOfScript', edited='$timestamp' WHERE pubID='$pubID'");
                $this->queryDatabase("UPDATE repo_code SET code='$scriptCode' WHERE pubID='$pubID'");
            }
            return array( 'postSuccess' => true, 'newID' => $pubID );
        }
    }
    public function generatePublicID(){
        // Dangerous, but meh. Likelyhood of it getting stuck in a loop approaches infintesimal values quickly.
        while(true){
            $character_set_array = array();
            $character_set_array[] = array('count' => 4, 'characters' => 'abcdefghijklmnopqrstuvwxyz');
            $character_set_array[] = array('count' => 2, 'characters' => '0123456789');
            $temp_array = array();
            foreach ($character_set_array as $character_set) {
                for ($i = 0; $i < $character_set['count']; $i++) {
                    $temp_array[] = $character_set['characters'][rand(0, strlen($character_set['characters']) - 1)];
                }
            }
            shuffle($temp_array);
            $outputID = implode('', $temp_array);
            $query = $this->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$outputID'");
            if($query->num_rows==0){ return $outputID; }
        }
    }
    public function isValidLogin($username, $password){
        $username = $this->databaseHandle->real_escape_string($username);
        $result = $this->queryDatabase("SELECT * FROM repo_users WHERE username='$username'");
        $row = $result->fetch_assoc();
        if($this->bCrypt->verify($password, $row['password'])){ return true; }else{ return false; }
    }
    public function isActiveUser($username){
        $username = $this->databaseHandle->real_escape_string($username);
        $result = $this->queryDatabase("SELECT * FROM repo_users WHERE username='$username'");
        $row = $result->fetch_assoc();
        if($row['status']==1){ return true; }else{ return false; }
    }
    public function queryDatabase($query){
        return $this->databaseHandle->query($query);
    }
    public function getResults($queryHandle, $numberPerPage, $pageNumber){
        $outputArray = array();
        $count = 0;
        $limiter = ($pageNumber-1)*$numberPerPage;
        while(count($outputArray)<$numberPerPage){
            if($count>=$limiter){
                $outputArray[count($outputArray)] = $queryHandle->fetch_assoc();
            }else{
                $queryHandle->fetch_assoc();
            }
            $count = $count+1;
        }
        return $outputArray;
    }
    public function searchForResults($query, $append="", $args=array()){
        $args = $args; // Disabled for now. Not sure how to handle it without making 16 fulltext indexes :/
        $queryTerm = $this->databaseHandle->real_escape_string($query);
        $queryString = "SELECT * FROM repo_entries WHERE MATCH (name,author,description,tags) AGAINST('$queryTerm' IN BOOLEAN MODE)".$append;
        $queryResult = $this->queryDatabase($queryString);
        return $queryResult;
    }
    public function redirect($newPage){
        header("Location: ".$this->mainSite.$newPage);
        exit;
    }
}
?>