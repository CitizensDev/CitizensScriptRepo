<?php

class ScriptRepo{
    public $mainSite = 'http://scripts.citizensnpcs.com/';
    public $loggedIn = false;
    public $admin = false;
    public $bCrypt;
    public $username;
    public $databaseHandle;
    public $ayah;
    protected $smarty;
    public function __construct(){
        $this->initSmarty();
        $this->populateVariables();
        $this->webStuff();
        $_SERVER['REQUEST_URI_PATH'] = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
        $path = explode('/', trim($_SERVER['REQUEST_URI_PATH'], '/'));
        array_shift($_GET);
        $this->handlePage($path);
    }
    public function populateVariables(){
        require('password.php');
        $this->databaseHandle = new mysqli('localhost', 'repo', $password, 'ScriptRepo');
        $this->bCrypt = new Bcrypt(12);
    }
    public function initSmarty(){
        $this->smarty = new Smarty;
        $this->smarty->setTemplateDir('/usr/share/nginx/www/scripts/assets/templates');
        $this->smarty->setCompileDir('/usr/share/nginx/www/scripts/assets/Smarty/templates_c');
        $this->smarty->setCacheDir('/usr/share/nginx/www/scripts/assets/Smarty/cache');
        $this->smarty->setConfigDir('/usr/share/nginx/www/scripts/assets/Smarty/configs');
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
            /*if(isset($_POST['1'])){ $query = $query."/1"; }else{ $query = $query."/0"; }
            if(isset($_POST['2'])){ $query = $query."/1"; }else{ $query = $query."/0"; }
            if(isset($_POST['3'])){ $query = $query."/1"; }else{ $query = $query."/0"; }
            if(isset($_POST['4'])){ $query = $query."/1"; }else{ $query = $query."/0"; } */
            $this->redirect('search/'.$query);
        }
    }
    public function handlePage($path){
        $variables = $this->getVariables($path);
        $this->smarty->assign($variables);
        $this->smarty->display('index.tpl');
    }
    public function getVariables($path){
        $variableArray = array();
        switch($path[0]){
            case 'credits':
                $variableArray = array('output' => 'credits.tpl');
                break;
            case 'download':
                $pubID = $this->databaseHandle->real_escape_string(strtolower($path[1]));
                $queryView = $this->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                if($queryView->num_rows==0){
                    $variableArray = array('output' => '404.tpl');
                }else{
                    $row = $queryView->fetch_assoc();
                    $newCount = $row['downloads']+1;
                    $this->queryDatabase("UPDATE repo_entries SET downloads='$newCount' WHERE pubID='$pubID'");
                    $queryCode = $this->queryDatabase("SELECT * FROM repo_code WHERE pubID='$pubID'");
                    $rowCode = $queryCode->fetch_assoc();
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename=script.yml');
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Type: application/octet-stream');
                    echo $rowCode['code'];
                    exit;
                }
                break;
            case 'raw':
                $pubID = $this->databaseHandle->real_escape_string(strtolower($path[1]));
                $queryView = $this->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                if($queryView->num_rows==0){
                    $variableArray = array('output' => '404.tpl');
                }else{
                    $row = $queryView->fetch_assoc();
                    $queryCode = $this->queryDatabase("SELECT * FROM repo_code WHERE pubID='$pubID'");
                    $rowCode = $queryCode->fetch_assoc();
                    $newCount = $row['downloads']+1;
                    $this->queryDatabase("UPDATE repo_entries SET downloads='$newCount' WHERE pubID='$pubID'");
                    echo "<html><body><pre>".htmlspecialchars($rowCode['code'])."</pre></body></html";
                    exit;
                }
                break;
            case 'login':
                $newArray = array(
                    'loginError' => false,
                    'loginMessage' => false,
                    'passwordError' => false,
                    'userError' => false,
                    'loginInfo' => false,
                    'username' => false,
                    'activePage' => 'login.',
                    'output' => 'login.tpl'
                );
                $variableArray = array_merge($variableArray, $newArray);
                if(isset($_SESSION['loginInfo'])){
                    $variableArray = array_merge($variableArray, array('loginInfo' => $_SESSION['loginInfo']));
                    unset($_SESSION['loginInfo']);
                }
                if(isset($_SESSION['loginMessage'])){
                    $variableArray = array_merge($variableArray, array('loginMessage' => $_SESSION['loginMessage']));
                    unset($_SESSION['loginMessage']);
                }
                if(isset($_POST['loginForm'])){
                    $loginSuccessful = $this->loginUser($_POST['username'], $_POST['password']);
                    if($loginSuccessful['loginSuccess']){
                        $_SESSION['username'] = $this->databaseHandle->real_escape_string($_POST['username']);
                        $_SESSION['loggedIn'] = true;
                        $this->redirect('');
                    }else{
                        $variableArray = array_merge($variableArray, $loginSuccessful);
                    }
                }elseif($this->loggedIn){
                    $this->redirect('user/'.$this->username);
                }
                break;
            case 'settings':
                $variableArray = array(
                    'output' => 'settings.tpl',
                    'successMessage' => false,
                );
                if(!$this->loggedIn){
                    $_SESSION['loginInfo'] = 'You must be logged in to change settings!';
                    $this->redirect('login');
                }
                if(isset($_POST['Save'])){
                    $variableArray = array_merge($variableArray, array('successMessage' => "Successfully updated your settings."));
                }
                break;
            case 'logout':
                session_destroy();
                session_start();
                $_SESSION['loginMessage'] = 'You have been successfully logged out.';
                $this->redirect('login');
                break;
            case 'resendconfirmation':
                $query = $this->queryDatabase("SELECT * FROM repo_users WHERE username='".$_SESSION['attemptedUsername']."'");
                $row = $query->fetch_assoc();
                $mailer = new Mailer();
                $mailer->sendConfirmationEmail($row['email'], $_SESSION['attemptedUsername']);
                $_SESSION['loginMessage'] = 'Confirmation email successfully sent to '.$row['email'].'!';
                $this->redirect('login');
                break;
            case 'register':
                include('password.php');
                $this->ayah = new AYAH($publisherKey, $scoringKey);
                $variableArray = array(
                    'activePage' => false,
                    'username' => false,
                    'email' => false,
                    'registerError' => false,
                    'usernameError' => false,
                    'emailError' => false,
                    'passwordError' => false,
                    'ayahError' => false,
                    'ayah' => $this->ayah->getPublisherHTML(),
                    'output' => 'register.tpl'
                );
                if(isset($_POST['registerForm'])){
                    $registrationArray = $this->registerUser($_POST);
                    if($registrationArray['registerSuccess']){
                        $_SESSION['loginMessage'] = 'You have now been registered, but must confirm your email before you can login.';
                        $this->redirect('login');
                    }else{
                        $variableArray = array_merge($variableArray, $registrationArray);
                    }
                }
                break;
            case 'post':
                $variableArray = array(
                    'activePage' => 'post',
                    'output' => 'post.tpl',
                    'postError' => false,
                    'scriptError' => false,
                    'scriptCode' => false,
                    'description' => false,
                    'descriptionError' => false,
                    'typeError' => false,
                    'tagError' => false,
                    'tags' => false,
                    'name' => false,
                    'nameError' => false,
                    'buttonSelected' => 1
                );
                if(!$this->loggedIn){
                    $_SESSION['loginInfo'] = 'You must be logged in to post new scripts!';
                    $this->redirect('login');
                }
                if(isset($_POST['SubmitScript'])){
                    $postSuccessful = $this->postScript($_POST);
                    if($postSuccessful['postSuccess']){
                        $this->redirect('view/'.$postSuccessful['newID']);
                    }else{
                        $variableArray = array_merge($variableArray, $postSuccessful);
                    }
                }
                if(isset($path[1])){
                    $idtoedit = $this->databaseHandle->real_escape_string($path[1]);
                    $queryEdit = $this->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$idtoedit'");
                    if($queryEdit->num_rows==0){ $this->redirect('post'); }
                    $queryCode = $this->queryDatabase("SELECT * FROM repo_code WHERE pubID='$idtoedit'");
                    $rowCode = $queryCode->fetch_assoc();
                    $row = $queryEdit->fetch_assoc();
                    $variableArray = array_merge($variableArray, array(
                        'name' => $row['name'],
                        'scriptCode' => $rowCode['code'],
                        'description' => $row['description'],
                        'tags' => $row['tags']
                    ));
                }
                break;
            case 'verify':
                $user = $this->databaseHandle->real_escape_string($path[1]);
                $query = $this->queryDatabase("SELECT * FROM repo_users WHERE username='$user' AND status=0");
                if(!isset($path[1]) || !isset($path[2]) || $path[2]!=md5($path[1]) || $query->num_rows===0){
                    // Something's wrong.
                    $this->redirect('');
                }else{
                    // Verify user
                    $this->queryDatabase("UPDATE repo_users SET status=1 WHERE username='$user'");
                    $_SESSION['loginMessage'] = 'You have successfully confirmed your email. You may now log in.';
                    $this->redirect('login');
                }
                break;
            case 'edit':
                $variableArray = array(
                    'postError' => false,
                    'nameError' => false,
                    'scriptError' => false,
                    'scriptCode' => false,
                    'description' => false,
                    'descriptionError' => false,
                    'typeError' => false,
                    'tagError' => false,
                    'tags' => false,
                    'name' => false
                );
                if(!$_SESSION['loggedIn']){
                    $_SESSION['loginInfo'] = 'You must be logged in to edit scripts!';
                    $this->redirect('login');
                }
                $pubID = $this->databaseHandle->real_escape_string($path[1]);
                $queryCheck = $this->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                if($queryCheck->num_rows==0){ $this->redirect(''); }
                $checkRow = $queryCheck->fetch_assoc();
                if($checkRow['author']!=$this->username && !$this->admin){ $this->redirect('post/'.$pubID); }
                if(isset($_POST['SubmitScript'])){
                    $editSuccess = $this->postScript($_POST, $pubID);
                    if($editSuccess['postSuccess']){
                        $this->redirect('view/'.$editSuccess['newID']);
                    }else{
                        $variableArray = array_merge($variableArray, $editSuccess);
                    }
                }else{
                    $queryCode = $connectionHandle->query("SELECT * FROM repo_code WHERE pubID='$pubID'");
                    $rowCode = $queryCode->fetch_assoc();
                    $variableArray = array_merge($variableArray, array(
                        'name' => $checkRow['name'],
                        'scriptCode' => $rowCode['code'],
                        'description' => $checkRow['description'],
                        'tags' => $checkRow['tags']
                    ));
                }
                break;
            case 'myscripts':
                if(!$_SESSION['loggedIn']){
                    $_SESSION['loginInfo'] = 'You must be logged in to edit scripts!';
                    $this->redirect('login');
                }
                $user = $this->username;
                $queryLikes = $this->queryDatabase("SELECT * FROM repo_likes");
                $likesArray = array();
                while($row = $queryLikes->fetch_assoc()){
                    if(!isset($likesArray[$row['pubID']])){ $likesArray[$row['pubID']] = 0; }
                    $likesArray[$row['pubID']] = $likesArray[$row['pubID']]+1;
                }
                $scriptQuery = $this->queryDatabase("SELECT * FROM repo_entries WHERE author='$user'");
                $scriptArray = array();
                while($row = $scriptQuery->fetch_assoc()){
                    $scriptArray[count($scriptArray)] = $row;
                }
                $variableArray = array(
                    'activePage' => false,
                    'output' => 'myscripts.tpl',
                    'resultArray' => $scriptArray,
                    'likesArray' => $likesArray
                );
                break;
            case 'search':
                if(!isset($path[1])){ $path[1] = null; } // Stop an undefined offset.
                $searchTerm = $this->databaseHandle->real_escape_string(urldecode($path[1]));
                $queryLikes = $this->queryDatabase("SELECT * FROM repo_likes");
                $likesArray = array();
                while($row = $queryLikes->fetch_assoc()){
                    if(!isset($likesArray[$row['pubID']])){ $likesArray[$row['pubID']] = 0; }
                    $likesArray[$row['pubID']] = $likesArray[$row['pubID']]+1;
                }
                $numberPerPage = 20;
                $pageNumber = 1;
                $resultPages = array(1, 2, 3, 4, 5);
                $append = "";
                $variableArray = array(
                    'listingType' => 'all',
                    'sortType' => 'mostLiked',
                    'searchTerm' => htmlspecialchars($searchTerm),
                    'searchTermURL' => str_replace(" ", "+", $searchTerm)
                );
                // Get the page number and number of results per page.
                if(isset($path[2])){
                    $typeOfSearch = $path[2];
                    switch($typeOfSearch){
                        case NULL:
                        case "":
                        case "all":
                            break;
                        case "citizen":
                        case "citizens":
                            $append = $append." AND scriptType=1";
                            $variableArray = array_merge($variableArray, array('listingType' => 'citizens'));
                            break;
                        case "denizen":
                        case "denizens":
                            $append = $append." AND scriptType=2";
                            $variableArray = array_merge($variableArray, array('listingType' => 'denizens'));
                            break;
                    }
                    if(isset($path[3])){
                        $sort = $path[3];
                        switch($sort){
                            case "newest":
                                $append = $append." ORDER BY timestamp DESC";
                                break;
                            case "oldest":
                                $append = $append." ORDER BY timestamp ASC";
                                $variableArray = array_merge($variableArray, array('sortType' => 'oldest'));
                                break;
                            case "mostLiked":
                                $append = $append." ORDER BY likes DESC";
                                $variableArray = array_merge($variableArray, array('sortType' => 'mostLiked'));
                                break;
                            case "mostViewed":
                                $append = $append." ORDER BY views DESC";
                                $variableArray = array_merge($variableArray, array('sortType' => 'mostViewed'));
                                break;
                            case "mostDownloads":
                                $append = $append." ORDER BY downloads DESC";
                                $variableArray = array_merge($variableArray, array('sortType' => 'mostDownloads'));
                                break;
                            default:
                                $append = $append." ORDER BY likes DESC";
                                $variableArray = array_merge($variableArray, array('sortType' => 'mostLiked'));
                                break;
                        }
                        if(isset($path[4])){
                            $pageNumber = intval($path[4]);
                            if(isset($path[5])){ $numberPerPage = intval($path[5]); }
                        }
                    }
                }
                if(!isset($path[3])){
                    $append = $append." ORDER BY likes DESC";
                }
                $querySearch = $this->searchForResults($searchTerm, $append);
                $variableArray = array_merge($variableArray, array('numResults' => $querySearch->num_rows));
                if($querySearch!=false && $numberPerPage!=0){
                    $numberOfPages = ceil($querySearch->num_rows/$numberPerPage);
                    $resultData = $this->getResults($querySearch, $numberPerPage, $pageNumber);
                }else{
                    $numberOfPages = 0;
                }
                if($numberOfPages<5){
                    $limit = $numberOfPages;
                    $start = 1;
                }elseif($pageNumber+2>$numberOfPages){
                    $limit = $numberOfPages;
                    $start = $pageNumber-(4-($numberOfPages-$pageNumber));
                }else{
                    $limit = $pageNumber+2;
                    $start = $pageNumber-2;
                }
                if($numberOfPages!=0){ $resultPages = range($start, $limit); }else{ $resultPages = array(1); }
                $variableArray = array_merge($variableArray, array(
                    'resultPageNumber' => $pageNumber,
                    'resultsPerPage' => $numberPerPage,
                    'resultPages' => $resultPages,
                    'searchQuery' => $searchTerm,
                    'output' => 'result.tpl',
                    'resultArray' => $resultData,
                    'likesArray' => $likesArray,
                    'activePage' => 'browse'
                ));
                break;
            case 'admin':
                if(!$this->loggedIn || !$this->admin){
                    $_SESSION['loginInfo'] = 'You must be logged in to do that!';
                    $this->redirect('login');
                }
                $flagQuery = $this->queryDatabase("SELECT * FROM repo_flags");
                $flagArray = array();
                while($row = $flagQuery->fetch_assoc()){
                    $count = count($flagArray);
                    $flagArray[$count] = $row;
                    if($row['type']==2){
                        $anotherQuery = $this->queryDatabase("SELECT * FROM repo_comments WHERE id='".$row['flaggedID']."'");
                        $anotherRow = $anotherQuery->fetch_assoc();
                        $flagArray[$count]['flaggedID'] = $anotherRow['entryID'];
                    }
                }
                $variableArray = array(
                    'flagArray' => $flagArray,
                    'activePage' => 'admin',
                    'output' => 'admin.tpl'
                );
                break;
            case 'support':
                $variableArray = array('output' => 'support.tpl');
                break;
            case 'browse':
                $variableArray = array(
                    'activePage' => 'browse',
                    'output' => 'browse.tpl',
                    'listingType' => 'all',
                    'sortType' => 'mostLiked'
                );
                $browseQuery = "SELECT * FROM repo_entries WHERE privacy=1";
                $numberPerPage = 20;
                $pageNumber = 1;
                $resultPages = array(1, 2, 3, 4, 5);
                // Get the page number and number of results per page.
                if(isset($path[1])){
                    $typeOfSearch = $path[1];
                    switch($typeOfSearch){
                        case NULL:
                        case "":
                        case "all":
                            break;
                        case "citizen":
                        case "citizens":
                            $browseQuery = $browseQuery." AND scriptType=1";
                            $variableArray = array_merge($variableArray, array('listingType' => 'citizens'));
                            break;
                        case "denizen":
                        case "denizens":
                            $browseQuery = $browseQuery." AND scriptType=2";
                            $variableArray = array_merge($variableArray, array('listingType' => 'denizens'));
                            break;
                    }
                    if(isset($path[2])){
                        $sort = $path[2];
                        switch($sort){
                            case "newest":
                                $browseQuery = $browseQuery." ORDER BY timestamp DESC";
                                break;
                            case "oldest":
                                $browseQuery = $browseQuery." ORDER BY timestamp ASC";
                                $variableArray = array_merge($variableArray, array('sortType' => 'oldest'));
                                break;
                            case "mostLiked":
                                $browseQuery = $browseQuery." ORDER BY likes DESC";
                                $variableArray = array_merge($variableArray, array('sortType' => 'mostLiked'));
                                break;
                            case "mostViewed":
                                $browseQuery = $browseQuery." ORDER BY views DESC";
                                $variableArray = array_merge($variableArray, array('sortType' => 'mostViewed'));
                                break;
                            case "mostDownloads":
                                $browseQuery = $browseQuery." ORDER BY downloads DESC";
                                $variableArray = array_merge($variableArray, array('sortType' => 'mostDownloads'));
                                break;
                            default:
                                $browseQuery = $browseQuery." ORDER BY likes DESC";
                                $variableArray = array_merge($variableArray, array('sortType' => 'mostLiked'));
                                break;
                        }
                        if(isset($path[3])){
                            $pageNumber = intval($path[3]);
                            if(isset($path[4])){ $numberPerPage = intval($path[4]); }
                        }
                    }
                }
                if(!isset($path[2])){
                    $browseQuery = $browseQuery." ORDER BY likes DESC";
                }
                $queryBrowse = $this->queryDatabase($browseQuery);
                $variableArray = array_merge($variableArray, array('numResults' => $queryBrowse->num_rows));
                if($queryBrowse!=false){
                    $numberOfPages = ceil($queryBrowse->num_rows/$numberPerPage);
                    $resultData = $this->getResults($queryBrowse, $numberPerPage, $pageNumber);
                    $variableArray = array_merge($variableArray, array('resultArray' => $resultData));
                }
                if($numberOfPages<5){
                    $limit = $numberOfPages;
                    $start = 1;
                }elseif($pageNumber+2>$numberOfPages){
                    $limit = $numberOfPages;
                    $start = $pageNumber-(4-($numberOfPages-$pageNumber));
                }else{
                    $limit = $pageNumber+2;
                    $start = $pageNumber-2;
                }
                if($numberOfPages!=0){ $resultPages = range($start, $limit); }else{ $resultPages = array(1); }
                $queryUsers = $this->queryDatabase("SELECT * FROM repo_users ORDER BY staff DESC, username");
                if($queryUsers!=false){ $userArray = $this->getResults($queryUsers, $numberPerPage, $pageNumber); }
                $variableArray = array_merge($variableArray, array(
                    'resultPageNumber' => $pageNumber,
                    'resultsPerPage' => $numberPerPage,
                    'resultPages' => $resultPages,
                    'userArray' => $userArray
                ));
                break;
            case 'view':
                $pubID = $this->databaseHandle->real_escape_string(strtolower($path[1]));
                $variableArray = array(
                    'commentField' => false,
                    'viewFailure' => false,
                    'viewSuccess' => false,
                    'output' => 'view.tpl',
                    'activePage' => 'view'
                );
                if(isset($_SESSION['viewSuccess'])){
                    $variableArray = array_merge($variableArray, array('viewSuccess' => $_SESSION['viewSuccess']));
                    unset($_SESSION['viewSuccess']);
                }
                if(isset($_SESSION['viewFailure'])){
                    $variableArray = array_merge($variableArray, array('viewFailure' => $_SESSION['viewFailure']));
                    unset($_SESSION['viewFailure']);
                }
                $user = $this->username;
                $query = $this->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                $queryCode = $this->queryDatabase("SELECT * FROM repo_code WHERE pubID='$pubID'");
                if($query->num_rows==0 && false){
                    $variableArray = array('output' => '404.tpl');
                }else{
                    if(isset($_POST['commentField'])){
                        if(!$this->loggedIn){
                            $_SESSION['loginInfo'] = 'You must be logged in to comment on scripts!';
                            $this->redirect('login');
                        }
                        $commentField = $this->databaseHandle->real_escape_string($_POST['commentField']);
                        if(strlen($commentField)<5){
                            $variableArray = array_merge($variableArray, array(
                                'viewFailure' => 'Please don\'t spam. Comments must be longer than 5 characters.',
                                'commentField' => $commentField
                            ));
                        }else{
                            $this->queryDatabase("INSERT INTO repo_comments (id, entryID, author, timestamp, content) VALUES ('NULL', '$pubID', '$user', now(), '$commentField')");
                            $variableArray = array_merge($variableArray, array( 'viewSuccess' => 'Your comment has been posted.' ));
                        }
                    }
                    $queryComments = $this->queryDatabase("SELECT * FROM repo_comments WHERE entryID='$pubID'");
                    $commentData = array();
                    while($row = $queryComments->fetch_assoc()){
                        $commentData[count($commentData)] = $row;
                    }
                    $queryLikes = $this->queryDatabase("SELECT * FROM repo_likes WHERE pubID='$pubID'");
                    $liked = false;
                    while($row = $queryLikes->fetch_assoc()){
                        if($this->loggedIn){ if($row['author']==$this->username){ $liked = true; } }
                    }
                    $data = $query->fetch_assoc();
                    $code = $queryCode->fetch_assoc();
                    $newviews = $data['views']+1;
                    $this->queryDatabase("UPDATE repo_entries SET views='$newviews' WHERE pubID='$pubID'");
                    $variableArray = array_merge($variableArray, array(
                        'likes' => $queryLikes->num_rows,
                        'liked' => $liked,
                        'dataToUse' => $data,
                        'dateCreated' => date('Y-m-d\TH:i:sO', $data['timestamp']),
                        'dateEdited' => date('Y-m-d\TH:i:sO', $data['edited']),
                        'code' => str_replace('<', '&lt;', $code['code']),
                        'commentData' => $commentData
                    ));
                }
                break;
            case 'user':
                if(!$_SESSION['loggedIn']){
                    $_SESSION['loginInfo'] = 'You must be logged in to view user profiles!';
                    $this->redirect('login');
                }
                $userToLookup = $this->databaseHandle->real_escape_string($path[1]);
                $userQuery = $this->queryDatabase("SELECT * FROM repo_users WHERE username='$userToLookup'");
                if($userQuery->num_rows!=1){
                    $variableArray = array('output' => '404.tpl');
                }else{
                    $queryLikes = $this->queryDatabase("SELECT * FROM repo_likes");
                    $userRow = $userQuery->fetch_assoc();
                    $likesArray = array();
                    while($row = $queryLikes->fetch_assoc()){
                        if(!isset($likesArray[$row['pubID']])){ $likesArray[$row['pubID']] = 0; }
                        $likesArray[$row['pubID']] = $likesArray[$row['pubID']]+1;
                    }
                    $scriptQuery = $this->queryDatabase("SELECT * FROM repo_entries WHERE author='$userToLookup'");
                    $scriptArray = array();
                    while($row = $scriptQuery->fetch_assoc()){
                        $scriptArray[count($scriptArray)] = $row;
                    }
                    $likeQuery = $this->queryDatabase("SELECT id FROM repo_likes WHERE author='$userToLookup'");
                    $commentQuery = $this->queryDatabase("SELECT id FROM repo_comments WHERE author='$userToLookup'");
                    $variableArray = array(
                        'likesArray' => $likesArray,
                        'resultArray' => $scriptArray,
                        'usernameForPage' => $userToLookup,
                        'output' => 'userpage.tpl',
                        'scriptsPosted' => count($scriptArray),
                        'commentsAdded' => $commentQuery->num_rows,
                        'scriptsLiked' => $likeQuery->num_rows,
                        'user' => $userRow
                    );
                }
                break;
            case 'index':
            case '':
            case 'home':
                $variableArray = array('output' => 'home.tpl', 'activePage' => 'home');
                break;
            case 'action':
                if(!isset($path[2])){
                    $this->redirect('');
                }
                if(!$this->loggedIn){
                    // If they submitted a comment without being logged in, reject it.
                    $_SESSION['loginInfo'] = 'You must be logged in to do that!';
                    $this->redirect('login');
                }
                $user = $this->username;
                if(in_array($path[1], array('1', '4', '5'))){
                    $pubID = $this->databaseHandle->real_escape_string($path[2]);
                    $existQuery = $this->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                    if($existQuery->num_rows==0){
                        $this->redirect('');
                    }
                }elseif($path[1]=="6"){
                    $commentID = $this->databaseHandle->real_escape_string($path[2]);
                    $existComment = $this->queryDatabase("SELECT * FROM repo_comments WHERE id='$commentID'");
                    if($existComment->num_rows==0){
                        $this->redirect('');
                    }
                }
                switch($path[1]){
                    case '1':
                        $queryLike = $this->queryDatabase("SELECT * FROM repo_likes WHERE pubID='$pubID' AND author='$user'");
                        if($queryLike->num_rows==0){
                            $existRow = $existQuery->fetch_assoc();
                            if(!isset($existRow)){ $existRow=0; }
                            $this->queryDatabase("UPDATE repo_entries SET likes='".($existRow+1)."' WHERE pubID='$pubID'");
                            $this->queryDatabase("INSERT INTO repo_likes (id, pubID, author) VALUES ('NULL', '$pubID', '$user')");
                            $_SESSION['viewSuccess'] = "You have successfully liked this script.";
                        }
                        $this->redirect('view/'.$pubID);
                        break;
                    case '4':
                        if(!$this->admin){
                            $this->redirect('');
                        }
                        $queryDelete = $this->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                        if($queryDelete->num_rows!=0){
                            $row = $queryDelete->fetch_assoc();
                            $this->queryDatabase("INSERT INTO repo_entries_deleted (id, pubID, author, name, description, tags, privacy, scriptType, timestamp, edited, downloads, views) VALUES ('NULL', '$pubID', '".$row['author']."', '".$row['name']."', '".$row['description']."', '".$row['tags']."', '".$row['privacy']."', '".$row['scriptType']."', '".$row['timestamp']."', '".$row['edited']."', '".$row['downloads']."', '".$row['views']."')");
                            $this->queryDatabase("DELETE FROM repo_entries WHERE pubID='$pubID'");
                            $queryCode = $this->queryDatabase("SELECT * FROM repo_code WHERE pubID='$pubID'");
                            $rowCode = $queryCode->fetch_assoc();
                            $this->queryDatabase("INSERT INTO repo_code_deleted (id, pubID, code) VALUES ('NULL', '$pubID', '".$rowCode['code']."')");
                            $this->queryDatabase("DELETE FROM repo_code WHERE pubID='$pubID'");
                        }
                        $this->redirect('');
                        break;
                    case '5':
                        $queryFlag = $this->queryDatabase("SELECT * FROM repo_flags WHERE type=1 AND flaggedID='$pubID' AND author='$user'");
                        if($queryFlag->num_rows==0){
                            $this->queryDatabase("INSERT INTO repo_flags (id, author, type, flaggedID) VALUES ('NULL', '$user', 1, '$pubID')");
                            $_SESSION['viewSuccess'] = "You have successfully flagged this script.";
                        }else{
                            $_SESSION['viewFailure'] = "You have already flagged this script!";
                        }
                        $this->redirect('view/'.$pubID);
                        break;
                    case '6':
                        $queryFlag = $this->queryDatabase("SELECT * FROM repo_flags WHERE type=2 AND flaggedID='$commentID' AND author='$user'");
                        if($queryFlag->num_rows==0){
                            $this->queryDatabase("INSERT INTO repo_flags (id, author, type, flaggedID) VALUES ('NULL', '$user', 2, '$commentID')");
                            $_SESSION['viewSuccess'] = "You have successfully flagged this comment.";
                        }else{
                            $_SESSION['viewFailure'] = "You have already flagged this comment!";
                        }
                        $existRow = $existComment->fetch_assoc();
                        $this->redirect('view/'.$existRow['entryID']);
                        break;
                }
                break;
            default:
                $variableArray = array('output' => '404.tpl');
                break;
        }
        return $variableArray;
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
            $description = $this->databaseHandle->real_escape_string($postData['Description']);
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
    private function redirect($newPage){
        header("Location: ".$this->mainSite.$newPage);
        exit;
    }
}
?>