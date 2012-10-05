<?php
class Pages {
    public $variableArray=array();
    public $mainClass;
    public $path;
    
    public function __construct(){
        // Something should go here someday.
    }
    public function __destruct(){
        // ...?
    }
    public function credits(){
        $this->variableArray = array('output' => 'credits.tpl');
    }
    public function download(){
        $pubID = $this->mainClass->databaseHandle->real_escape_string(strtolower($this->path[1]));
        $queryView = $this->mainClass->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
        if($queryView->num_rows==0){
            $this->page404();
        }else{
            $row = $queryView->fetch_assoc();
            $newCount = $row['downloads']+1;
            $this->mainClass->queryDatabase("UPDATE repo_entries SET downloads='$newCount' WHERE pubID='$pubID'");
            $queryCode = $this->mainClass->queryDatabase("SELECT * FROM repo_code WHERE pubID='$pubID'");
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
    }
    public function raw(){
        $pubID = $this->mainClass->databaseHandle->real_escape_string(strtolower($this->path[1]));
        $queryView = $this->mainClass->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
        if($queryView->num_rows==0){
            $this->variableArray = array('output' => '404.tpl');
        }else{
            $row = $queryView->fetch_assoc();
            $queryCode = $this->mainClass->queryDatabase("SELECT * FROM repo_code WHERE pubID='$pubID'");
            $rowCode = $queryCode->fetch_assoc();
            $newCount = $row['downloads']+1;
            $this->mainClass->queryDatabase("UPDATE repo_entries SET downloads='$newCount' WHERE pubID='$pubID'");
            echo "<html><body><pre>".htmlspecialchars($rowCode['code'])."</pre></body></html";
            exit;
        }
    }
    public function login(){
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
        $this->variableArray = array_merge($this->variableArray, $newArray);
                if(isset($_SESSION['loginInfo'])){
                    $this->variableArray = array_merge($this->variableArray, array('loginInfo' => $_SESSION['loginInfo']));
                    unset($_SESSION['loginInfo']);
                }
                if(isset($_SESSION['loginMessage'])){
                    $this->variableArray = array_merge($this->variableArray, array('loginMessage' => $_SESSION['loginMessage']));
                    unset($_SESSION['loginMessage']);
                }
                if(isset($_POST['loginForm'])){
                    $loginSuccessful = $this->mainClass->loginUser($_POST['username'], $_POST['password']);
                    if($loginSuccessful['loginSuccess']){
                        $_SESSION['username'] = $this->mainClass->databaseHandle->real_escape_string($_POST['username']);
                        $_SESSION['loggedIn'] = true;
                        $this->mainClass->redirect('');
                    }else{
                        $this->variableArray = array_merge($this->variableArray, $loginSuccessful);
                    }
                }elseif($this->mainClass->loggedIn){
                    $this->mainClass->redirect('user/'.$this->username);
                }
    }
    public function logout(){
        session_destroy();
        session_start();
        $_SESSION['loginMessage'] = 'You have been successfully logged out.';
        $this->mainClass->redirect('login');
    }
    public function settings(){
        $this->variableArray = array(
            'output' => 'settings.tpl',
            'successMessage' => false,
        );
        if(!$this->mainClass->loggedIn){
            $_SESSION['loginInfo'] = 'You must be logged in to change settings!';
            $this->mainClass->redirect('login');
        }
        if(isset($_POST['Save'])){
            $this->variableArray = array_merge($this->variableArray, array('successMessage' => "Successfully updated your settings."));
        }
    }
    public function resendconfirmation(){
        $query = $this->mainClass->queryDatabase("SELECT * FROM repo_users WHERE username='".$_SESSION['attemptedUsername']."'");
        $row = $query->fetch_assoc();
        $mailer = new Mailer();
        $mailer->sendConfirmationEmail($row['email'], $_SESSION['attemptedUsername']);
        $_SESSION['loginMessage'] = 'Confirmation email successfully sent to '.$row['email'].'!';
        $this->mainClass->redirect('login');
    }
    public function home(){
        $this->variableArray = array('output' => 'home.tpl', 'activePage' => 'home');
    }
    public function register(){
        include('password.php');
        $this->mainClass->ayah = new AYAH($publisherKey, $scoringKey);
        $this->variableArray = array(
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
            $registrationArray = $this->mainClass->registerUser($_POST);
            if($registrationArray['registerSuccess']){
                $_SESSION['loginMessage'] = 'You have now been registered, but must confirm your email before you can login.';
                $this->mainClass->redirect('login');
            }else{
                $this->variableArray = array_merge($this->variableArray, $registrationArray);
            }
        }
    }
    public function post(){
        $this->variableArray = array(
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
        if(!$this->mainClass->loggedIn){
            $_SESSION['loginInfo'] = 'You must be logged in to post new scripts!';
            $this->mainClass->redirect('login');
        }
        if(isset($_POST['SubmitScript'])){
            $postSuccessful = $this->mainClass->postScript($_POST);
            if($postSuccessful['postSuccess']){
                $this->mainClass->redirect('view/'.$postSuccessful['newID']);
            }else{
                $this->variableArray = array_merge($this->variableArray, $postSuccessful);
            }
        }
        if(isset($this->path[1])){
            $idtoedit = $this->mainClass->databaseHandle->real_escape_string($this->path[1]);
            $queryEdit = $this->mainClass->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$idtoedit'");
            if($queryEdit->num_rows==0){ $this->mainClass->redirect('post'); }
            $queryCode = $this->mainClass->queryDatabase("SELECT * FROM repo_code WHERE pubID='$idtoedit'");
            $rowCode = $queryCode->fetch_assoc();
            $row = $queryEdit->fetch_assoc();
            $this->variableArray = array_merge($this->variableArray, array(
                'name' => $row['name'],
                'scriptCode' => $rowCode['code'],
                'description' => $row['description'],
                'tags' => $row['tags']
            ));
        }
    }
    public function verify(){
        $user = $this->mainClass->databaseHandle->real_escape_string($this->path[1]);
        $query = $this->mainClass->queryDatabase("SELECT * FROM repo_users WHERE username='$user' AND status=0");
        if(!isset($this->path[1]) || !isset($this->path[2]) || $this->path[2]!=md5($this->path[1]) || $query->num_rows===0){
            // Something's wrong.
            $this->mainClass->redirect('');
        }else{
            // Verify user
            $this->mainClass->queryDatabase("UPDATE repo_users SET status=1 WHERE username='$user'");
            $_SESSION['loginMessage'] = 'You have successfully confirmed your email. You may now log in.';
            $this->mainClass->redirect('login');
        }
    }
    public function edit(){
        $this->variableArray = array(
                    'postError' => false,
                    'nameError' => false,
                    'scriptError' => false,
                    'scriptCode' => false,
                    'description' => false,
                    'descriptionError' => false,
                    'typeError' => false,
                    'tagError' => false,
                    'tags' => false,
                    'name' => false,
                    'output' => 'post.tpl'
                );
                if(!$_SESSION['loggedIn']){
                    $_SESSION['loginInfo'] = 'You must be logged in to edit scripts!';
                    $this->mainClass->redirect('login');
                }
                $pubID = $this->mainClass->databaseHandle->real_escape_string($this->path[1]);
                $queryCheck = $this->mainClass->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                if($queryCheck->num_rows==0){ $this->mainClass->redirect(''); }
                $checkRow = $queryCheck->fetch_assoc();
                if($checkRow['author']!=$this->username && !$this->admin){ $this->mainClass->redirect('post/'.$pubID); }
                if(isset($_POST['SubmitScript'])){
                    $editSuccess = $this->mainClass->postScript($_POST, $pubID);
                    if($editSuccess['postSuccess']){
                        $this->mainClass->redirect('view/'.$editSuccess['newID']);
                    }else{
                        $this->variableArray = array_merge($this->variableArray, $editSuccess);
                    }
                }else{
                    $queryCode = $this->mainClass->queryDatabase("SELECT * FROM repo_code WHERE pubID='$pubID'");
                    $rowCode = $queryCode->fetch_assoc();
                    $this->variableArray = array_merge($this->variableArray, array(
                        'name' => $checkRow['name'],
                        'scriptCode' => $rowCode['code'],
                        'description' => $checkRow['description'],
                        'tags' => $checkRow['tags']
                    ));
                }
    }
    public function myscripts(){
        if(!$_SESSION['loggedIn']){
            $_SESSION['loginInfo'] = 'You must be logged in to edit scripts!';
            $this->mainClass->redirect('login');
        }
        $user = $this->mainClass->username;
        $queryLikes = $this->mainClass->queryDatabase("SELECT * FROM repo_likes");
        $likesArray = array();
        while($row = $queryLikes->fetch_assoc()){
            if(!isset($likesArray[$row['pubID']])){ $likesArray[$row['pubID']] = 0; }
            $likesArray[$row['pubID']] = $likesArray[$row['pubID']]+1;
        }
        $scriptQuery = $this->mainClass->queryDatabase("SELECT * FROM repo_entries WHERE author='$user'");
        $scriptArray = array();
        while($row = $scriptQuery->fetch_assoc()){
            $scriptArray[count($scriptArray)] = $row;
        }
        $this->variableArray = array(
            'activePage' => false,
            'output' => 'myscripts.tpl',
            'resultArray' => $scriptArray,
            'likesArray' => $likesArray
        );
    }
    public function search(){
        if(!isset($this->path[1])){ $this->path[1] = null; } // Stop an undefined offset.
        $searchTerm = $this->mainClass->databaseHandle->real_escape_string(urldecode($this->path[1]));
        $queryLikes = $this->mainClass->queryDatabase("SELECT * FROM repo_likes");
        $likesArray = array();
        while($row = $queryLikes->fetch_assoc()){
            if(!isset($likesArray[$row['pubID']])){ $likesArray[$row['pubID']] = 0; }
            $likesArray[$row['pubID']] = $likesArray[$row['pubID']]+1;
        }
        $numberPerPage = 20;
        $pageNumber = 1;
        $resultPages = array(1, 2, 3, 4, 5);
        $append = "";
        $this->variableArray = array(
            'listingType' => 'all',
            'sortType' => 'mostLiked',
            'searchTerm' => htmlspecialchars($searchTerm),
            'searchTermURL' => str_replace(" ", "+", $searchTerm)
        );
        // Get the page number and number of results per page.
        if(isset($this->path[2])){
            $typeOfSearch = $this->path[2];
            switch($typeOfSearch){
                case NULL:
                case "":
                case "all":
                    break;
                case "citizen":
                case "citizens":
                    $append = $append." AND scriptType=1";
                    $this->variableArray = array_merge($this->variableArray, array('listingType' => 'citizens'));
                    break;
                case "denizen":
                case "denizens":
                    $append = $append." AND scriptType=2";
                    $this->variableArray = array_merge($this->variableArray, array('listingType' => 'denizens'));
                    break;
            }
            if(isset($this->path[3])){
                $sort = $this->path[3];
                switch($sort){
                    case "newest":
                        $append = $append." ORDER BY timestamp DESC";
                        break;
                    case "oldest":
                        $append = $append." ORDER BY timestamp ASC";
                        $this->variableArray = array_merge($this->variableArray, array('sortType' => 'oldest'));
                        break;
                    case "mostLiked":
                        $append = $append." ORDER BY likes DESC";
                        $this->variableArray = array_merge($this->variableArray, array('sortType' => 'mostLiked'));
                        break;
                    case "mostViewed":
                        $append = $append." ORDER BY views DESC";
                        $this->variableArray = array_merge($this->variableArray, array('sortType' => 'mostViewed'));
                        break;
                    case "mostDownloads":
                        $append = $append." ORDER BY downloads DESC";
                        $this->variableArray = array_merge($this->variableArray, array('sortType' => 'mostDownloads'));
                        break;
                    default:
                        $append = $append." ORDER BY likes DESC";
                        $this->variableArray = array_merge($this->variableArray, array('sortType' => 'mostLiked'));
                        break;
                }
                if(isset($this->path[4])){
                    $pageNumber = intval($this->path[4]);
                    if(isset($this->path[5])){ $numberPerPage = intval($this->path[5]); }
                }
            }
        }
        if(!isset($this->path[3])){
           $append = $append." ORDER BY likes DESC";
        }
        $querySearch = $this->mainClass->searchForResults($searchTerm, $append);
        $this->variableArray = array_merge($this->variableArray, array('numResults' => $querySearch->num_rows));
        if($querySearch!=false && $numberPerPage!=0){
            $numberOfPages = ceil($querySearch->num_rows/$numberPerPage);
            $resultData = $this->mainClass->getResults($querySearch, $numberPerPage, $pageNumber);
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
        $this->variableArray = array_merge($this->variableArray, array(
            'resultPageNumber' => $pageNumber,
            'resultsPerPage' => $numberPerPage,
            'resultPages' => $resultPages,
            'searchQuery' => $searchTerm,
            'output' => 'result.tpl',
            'resultArray' => $resultData,
            'likesArray' => $likesArray,
            'activePage' => 'browse'
        ));
    }
    public function admin(){
        if(!$this->mainClass->loggedIn || !$this->mainClass->admin){
            $_SESSION['loginInfo'] = 'You must be logged in to do that!';
            $this->mainClass->redirect('login');
        }
        $flagQuery = $this->mainClass->queryDatabase("SELECT * FROM repo_flags");
        $flagArray = array();
        while($row = $flagQuery->fetch_assoc()){
            $count = count($flagArray);
            $flagArray[$count] = $row;
            if($row['type']==2){
                $anotherQuery = $this->mainClass->queryDatabase("SELECT * FROM repo_comments WHERE id='".$row['flaggedID']."'");
                $anotherRow = $anotherQuery->fetch_assoc();
                $flagArray[$count]['flaggedID'] = $anotherRow['entryID'];
            }
        }
        $this->variableArray = array(
            'flagArray' => $flagArray,
            'activePage' => 'admin',
            'output' => 'admin.tpl'
        );
    }
    public function support(){
        $this->variableArray = array('output' => 'support.tpl');
    }
    public function browse(){
        $this->variableArray = array(
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
                if(isset($this->path[1])){
                    $typeOfSearch = $this->path[1];
                    switch($typeOfSearch){
                        case NULL:
                        case "":
                        case "all":
                            break;
                        case "citizen":
                        case "citizens":
                            $browseQuery = $browseQuery." AND scriptType=1";
                            $this->variableArray = array_merge($this->variableArray, array('listingType' => 'citizens'));
                            break;
                        case "denizen":
                        case "denizens":
                            $browseQuery = $browseQuery." AND scriptType=2";
                            $this->variableArray = array_merge($this->variableArray, array('listingType' => 'denizens'));
                            break;
                    }
                    if(isset($this->path[2])){
                        $sort = $this->path[2];
                        switch($sort){
                            case "newest":
                                $browseQuery = $browseQuery." ORDER BY timestamp DESC";
                                break;
                            case "oldest":
                                $browseQuery = $browseQuery." ORDER BY timestamp ASC";
                                $this->variableArray = array_merge($this->variableArray, array('sortType' => 'oldest'));
                                break;
                            case "mostLiked":
                                $browseQuery = $browseQuery." ORDER BY likes DESC";
                                $this->variableArray = array_merge($this->variableArray, array('sortType' => 'mostLiked'));
                                break;
                            case "mostViewed":
                                $browseQuery = $browseQuery." ORDER BY views DESC";
                                $this->variableArray = array_merge($this->variableArray, array('sortType' => 'mostViewed'));
                                break;
                            case "mostDownloads":
                                $browseQuery = $browseQuery." ORDER BY downloads DESC";
                                $this->variableArray = array_merge($this->variableArray, array('sortType' => 'mostDownloads'));
                                break;
                            default:
                                $browseQuery = $browseQuery." ORDER BY likes DESC";
                                $this->variableArray = array_merge($this->variableArray, array('sortType' => 'mostLiked'));
                                break;
                        }
                        if(isset($this->path[3])){
                            $pageNumber = intval($this->path[3]);
                            if(isset($this->path[4])){ $numberPerPage = intval($this->path[4]); }
                        }
                    }
                }
                if(!isset($this->path[2])){
                    $browseQuery = $browseQuery." ORDER BY likes DESC";
                }
                $queryBrowse = $this->mainClass->queryDatabase($browseQuery);
                $this->variableArray = array_merge($this->variableArray, array('numResults' => $queryBrowse->num_rows));
                if($queryBrowse!=false){
                    $numberOfPages = ceil($queryBrowse->num_rows/$numberPerPage);
                    $resultData = $this->mainClass->getResults($queryBrowse, $numberPerPage, $pageNumber);
                    $this->variableArray = array_merge($this->variableArray, array('resultArray' => $resultData));
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
                $queryUsers = $this->mainClass->queryDatabase("SELECT * FROM repo_users ORDER BY staff DESC, username");
                if($queryUsers!=false){ $userArray = $this->mainClass->getResults($queryUsers, $numberPerPage, $pageNumber); }
                $this->variableArray = array_merge($this->variableArray, array(
                    'resultPageNumber' => $pageNumber,
                    'resultsPerPage' => $numberPerPage,
                    'resultPages' => $resultPages,
                    'userArray' => $userArray
                ));
    }
    public function view(){
        $pubID = $this->mainClass->databaseHandle->real_escape_string(strtolower($this->path[1]));
                $this->variableArray = array(
                    'commentField' => false,
                    'viewFailure' => false,
                    'viewSuccess' => false,
                    'output' => 'view.tpl',
                    'activePage' => 'view'
                );
                if(isset($_SESSION['viewSuccess'])){
                    $this->variableArray = array_merge($this->variableArray, array('viewSuccess' => $_SESSION['viewSuccess']));
                    unset($_SESSION['viewSuccess']);
                }
                if(isset($_SESSION['viewFailure'])){
                    $this->variableArray = array_merge($this->variableArray, array('viewFailure' => $_SESSION['viewFailure']));
                    unset($_SESSION['viewFailure']);
                }
                $user = $this->mainClass->username;
                $query = $this->mainClass->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                $queryCode = $this->mainClass->queryDatabase("SELECT * FROM repo_code WHERE pubID='$pubID'");
                if($query->num_rows==0 && false){
                    $this->variableArray = array('output' => '404.tpl');
                }else{
                    if(isset($_POST['commentField'])){
                        if(!$this->mainClass->loggedIn){
                            $_SESSION['loginInfo'] = 'You must be logged in to comment on scripts!';
                            $this->mainClass->redirect('login');
                        }
                        $commentField = $this->mainClass->databaseHandle->real_escape_string($_POST['commentField']);
                        if(strlen($commentField)<5){
                            $this->variableArray = array_merge($this->variableArray, array(
                                'viewFailure' => 'Please don\'t spam. Comments must be longer than 5 characters.',
                                'commentField' => $commentField
                            ));
                        }else{
                            $this->mainClass->queryDatabase("INSERT INTO repo_comments (id, entryID, author, timestamp, content) VALUES ('NULL', '$pubID', '$user', now(), '$commentField')");
                            $this->variableArray = array_merge($this->variableArray, array( 'viewSuccess' => 'Your comment has been posted.' ));
                        }
                    }
                    $queryComments = $this->mainClass->queryDatabase("SELECT * FROM repo_comments WHERE entryID='$pubID'");
                    $commentData = array();
                    while($row = $queryComments->fetch_assoc()){
                        $commentData[count($commentData)] = $row;
                    }
                    $queryLikes = $this->mainClass->queryDatabase("SELECT * FROM repo_likes WHERE pubID='$pubID'");
                    $liked = false;
                    while($row = $queryLikes->fetch_assoc()){
                        if($this->mainClass->loggedIn){ if($row['author']==$this->mainClass->username){ $liked = true; } }
                    }
                    $data = $query->fetch_assoc();
                    $code = $queryCode->fetch_assoc();
                    $newviews = $data['views']+1;
                    $this->mainClass->queryDatabase("UPDATE repo_entries SET views='$newviews' WHERE pubID='$pubID'");
                    $this->variableArray = array_merge($this->variableArray, array(
                        'likes' => $queryLikes->num_rows,
                        'liked' => $liked,
                        'dataToUse' => $data,
                        'dateCreated' => date('Y-m-d\TH:i:sO', $data['timestamp']),
                        'dateEdited' => date('Y-m-d\TH:i:sO', $data['edited']),
                        'code' => str_replace('<', '&lt;', $code['code']),
                        'commentData' => $commentData
                    ));
                }
    }
    public function user(){
        $userToLookup = $this->mainClass->databaseHandle->real_escape_string($this->path[1]);
                $userQuery = $this->mainClass->queryDatabase("SELECT * FROM repo_users WHERE username='$userToLookup'");
                if($userQuery->num_rows!=1){
                    $this->variableArray = array('output' => '404.tpl');
                }else{
                    $queryLikes = $this->mainClass->queryDatabase("SELECT * FROM repo_likes");
                    $userRow = $userQuery->fetch_assoc();
                    $likesArray = array();
                    while($row = $queryLikes->fetch_assoc()){
                        if(!isset($likesArray[$row['pubID']])){ $likesArray[$row['pubID']] = 0; }
                        $likesArray[$row['pubID']] = $likesArray[$row['pubID']]+1;
                    }
                    $scriptQuery = $this->mainClass->queryDatabase("SELECT * FROM repo_entries WHERE author='$userToLookup'");
                    $scriptArray = array();
                    while($row = $scriptQuery->fetch_assoc()){
                        $scriptArray[count($scriptArray)] = $row;
                    }
                    $likeQuery = $this->mainClass->queryDatabase("SELECT id FROM repo_likes WHERE author='$userToLookup'");
                    $commentQuery = $this->mainClass->queryDatabase("SELECT id FROM repo_comments WHERE author='$userToLookup'");
                    $this->variableArray = array(
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
    }
    public function action(){
        if(!isset($this->path[2])){
                    $this->mainClass->redirect('');
                }
                if(!$this->mainClass->loggedIn){
                    // If they submitted a comment without being logged in, reject it.
                    $_SESSION['loginInfo'] = 'You must be logged in to do that!';
                    $this->mainClass->redirect('login');
                }
                $user = $this->mainClass->username;
                if(in_array($this->path[1], array('1', '4', '5'))){
                    $pubID = $this->mainClass->databaseHandle->real_escape_string($this->path[2]);
                    $existQuery = $this->mainClass->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                    if($existQuery->num_rows==0){
                        $this->mainClass->redirect('');
                    }
                }elseif($this->path[1]=="6"){
                    $commentID = $this->mainClass->databaseHandle->real_escape_string($this->path[2]);
                    $existComment = $this->mainClass->queryDatabase("SELECT * FROM repo_comments WHERE id='$commentID'");
                    if($existComment->num_rows==0){
                        $this->mainClass->redirect('');
                    }
                }
                switch($this->path[1]){
                    case '1':
                        $queryLike = $this->mainClass->queryDatabase("SELECT * FROM repo_likes WHERE pubID='$pubID' AND author='$user'");
                        if($queryLike->num_rows==0){
                            $existRow = $existQuery->fetch_assoc();
                            $this->mainClass->queryDatabase("UPDATE repo_entries SET likes='".($existRow['likes']+1)."' WHERE pubID='$pubID'");
                            $this->mainClass->queryDatabase("INSERT INTO repo_likes (id, pubID, author) VALUES ('NULL', '$pubID', '$user')");
                            $_SESSION['viewSuccess'] = "You have successfully liked this script.";
                        }
                        $this->mainClass->redirect('view/'.$pubID);
                        break;
                    case '4':
                        if(!$this->admin){
                            $this->mainClass->redirect('');
                        }
                        $queryDelete = $this->mainClass->queryDatabase("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                        if($queryDelete->num_rows!=0){
                            $row = $queryDelete->fetch_assoc();
                            $this->mainClass->queryDatabase("INSERT INTO repo_entries_deleted (id, pubID, author, name, description, tags, privacy, scriptType, timestamp, edited, downloads, views) VALUES ('NULL', '$pubID', '".$row['author']."', '".$row['name']."', '".$row['description']."', '".$row['tags']."', '".$row['privacy']."', '".$row['scriptType']."', '".$row['timestamp']."', '".$row['edited']."', '".$row['downloads']."', '".$row['views']."')");
                            $this->mainClass->queryDatabase("DELETE FROM repo_entries WHERE pubID='$pubID'");
                            $queryCode = $this->mainClass->queryDatabase("SELECT * FROM repo_code WHERE pubID='$pubID'");
                            $rowCode = $queryCode->fetch_assoc();
                            $this->mainClass->queryDatabase("INSERT INTO repo_code_deleted (id, pubID, code) VALUES ('NULL', '$pubID', '".$rowCode['code']."')");
                            $this->mainClass->queryDatabase("DELETE FROM repo_code WHERE pubID='$pubID'");
                        }
                        $this->mainClass->redirect('');
                        break;
                    case '5':
                        $queryFlag = $this->mainClass->queryDatabase("SELECT * FROM repo_flags WHERE type=1 AND flaggedID='$pubID' AND author='$user'");
                        if($queryFlag->num_rows==0){
                            $this->mainClass->queryDatabase("INSERT INTO repo_flags (id, author, type, flaggedID) VALUES ('NULL', '$user', 1, '$pubID')");
                            $_SESSION['viewSuccess'] = "You have successfully flagged this script.";
                        }else{
                            $_SESSION['viewFailure'] = "You have already flagged this script!";
                        }
                        $this->redirect('view/'.$pubID);
                        break;
                    case '6':
                        $queryFlag = $this->mainClass->queryDatabase("SELECT * FROM repo_flags WHERE type=2 AND flaggedID='$commentID' AND author='$user'");
                        if($queryFlag->num_rows==0){
                            $this->mainClass->queryDatabase("INSERT INTO repo_flags (id, author, type, flaggedID) VALUES ('NULL', '$user', 2, '$commentID')");
                            $_SESSION['viewSuccess'] = "You have successfully flagged this comment.";
                        }else{
                            $_SESSION['viewFailure'] = "You have already flagged this comment!";
                        }
                        $existRow = $existComment->fetch_assoc();
                        $this->mainClass->redirect('view/'.$existRow['entryID']);
                        break;
                }
    }
    public function page404(){
        $this->variableArray = array('output' => '404.tpl');
    }
}

?>
