<?php
ini_set('display_errors', 'On');
session_start();
if(!isset($_SESSION['loggedIn'])){ $_SESSION['loggedIn'] = false; }
if(!isset($_SESSION['admin'])){ $_SESSION['admin'] = false; }
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');

// Get the arguments from the url
$_SERVER['REQUEST_URI_PATH'] = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
$path = explode('/', trim($_SERVER['REQUEST_URI_PATH'], '/'));
array_shift($_GET);

// Mailer
require_once('assets/phpmailer/mail.php');

// AreYouAHuman
require_once('assets/ayah.php');

function alphaID($in, $to_num = false, $pad_up = false){
/*
 * Translates a number to a short alhanumeric version
 * 
 * @author    Kevin van Zonneveld <kevin@vanzonneveld.net>
 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
 * @version   SVN: Release: $Id: alphaID.inc.php 344 2009-06-10 17:43:59Z kevin $
 * @link      http://kevin.vanzonneveld.net/
 * 
 * @param mixed   $in     String or long input to translate     
 * @param boolean $to_num Reverses translation when true
 * @param mixed   $pad_up Number or boolean padds the result up to a specified length
 * 
 * @return mixed string or long
 */
    $index = "abcdefghijklmnopqrstuvwxyz0123456789";
    $base  = strlen($index);
 
    if ($to_num) {
        // Digital number  <<--  alphabet letter code
        $in  = strrev($in);
        $out = 0;
        $len = strlen($in) - 1;
        for ($t = 0; $t <= $len; $t++) {
            $bcpow = bcpow($base, $len - $t);
            $out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
        }
 
        if (is_numeric($pad_up)) {
            $pad_up--;
            if ($pad_up > 0) {
                $out -= pow($base, $pad_up);
            }
        }
    } else { 
        // Digital number  -->>  alphabet letter code
        if (is_numeric($pad_up)) {
            $pad_up--;
            if ($pad_up > 0) {
                $in += pow($base, $pad_up);
            }
        }
 
        $out = "";
        for ($t = floor(log10($in) / log10($base)); $t >= 0; $t--) {
            $a   = floor($in / bcpow($base, $t));
            $out = $out . substr($index, $a, 1);
            $in  = $in - ($a * bcpow($base, $t));
        }
        $out = strrev($out); // reverse
    }
 
    return $out;
}
function validEmail($email){
    /*
     * Email validation function. Credit to http://www.linuxjournal.com/article/9585.
     */
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if
(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || 
 ↪checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}
function random_string(){
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
    return implode('', $temp_array);
}
function createPubID(){
    $connectionHandle = $GLOBALS['connectionHandle'];
    // Dangerous, but meh. Likelyhood of it getting stuck in a loop approaches infintesimal values quickly.
    while(true){
        $outputID = random_string();
        $query = $connectionHandle->query("SELECT * FROM repo_entries WHERE pubID='$outputID'");
        if($query->num_rows==0){ return $outputID; }
    }
}

// Handle search queries in the string
if(isset($_POST['q'])){
    $query = str_replace(array("%20", " "), "+", $_POST['q']);
    header('Location: http://scripts.citizensnpcs.com/search/'.$query.'/1/1/1/1');
    exit;
}
if(isset($_POST['q2'])){
    $query = str_replace(array("%20", " "), "+", $_POST['searchBox']);
    if(isset($_POST['1'])){
        // Users
        $query = $query."/1";
    }else{
        $query = $query."/0";
    }
    if(isset($_POST['2'])){
        // Code
        $query = $query."/1";
    }else{
        $query = $query."/0";
    }
    if(isset($_POST['3'])){
        // Tags
        $query = $query."/1";
    }else{
        $query = $query."/0";
    }
    if(isset($_POST['4'])){
        // Descriptions
        $query = $query."/1";
    }else{
        $query = $query."/0";
    }
    header('Location: http://scripts.citizensnpcs.com/search/'.$query);
    exit;
}
function getCurrentTimeZone($username){
    $username = htmlspecialchars($username);
    $query = $GLOBALS['connectionHandle']->query("SELECT * FROM repo_users WHERE username='$username'");
    if($query!=false){
        $row = $query->fetch_assoc();
        return trim($row['timezone']);
    }
}
function getTimeZoneOptions($active){
    $timezone_identifiers = DateTimeZone::listIdentifiers();
    $selected = '';
    $data = null;
    $continent = null;
    foreach( $timezone_identifiers as $value ){
        if ( preg_match( '/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $value ) ){
            $ex=explode("/",$value);//obtain continent,city
            if ($continent!=$ex[0]){
                if ($continent!="") $data = $data.'</optgroup>';
                    $data = $data.'<optgroup label="'.$ex[0].'">';
            }
 
            $city=$ex[1];
            $continent=$ex[0];
                if($value==$active){ $selected='selected="selected"'; }
                if(isset($ex[2])){ $city = implode("/",array($ex[1], $ex[2])); }
            $data = $data.'<option value="'.$value.'" '.$selected.'>'.$city.'</option>';                
        }
            $selected = '';
    }
    return $data;
}
function getResults($queryHandle, $numberToGet, $pageNumber){
    $outputArray = array();
    while(count($outputArray)<$numberToGet){
        $outputArray[count($outputArray)] = $queryHandle->fetch_assoc();
    }
    return $outputArray;
}
// GeSHi
require_once('assets/geshi.php');

// Smarty
include('assets/Smarty/Smarty.class.php');
$smarty = new Smarty;
$smarty->setTemplateDir('/usr/share/nginx/www/scripts/assets/templates');
$smarty->setCompileDir('/usr/share/nginx/www/scripts/assets/Smarty/templates_c');
$smarty->setCacheDir('/usr/share/nginx/www/scripts/assets/Smarty/cache');
$smarty->setConfigDir('/usr/share/nginx/www/scripts/assets/Smarty/configs');
$smarty->assign('loggedIn', $_SESSION['loggedIn']);
$smarty->assign('admin', $_SESSION['admin']);
$smarty->assign('adminNeeded', false);
if($_SESSION['loggedIn']){ $smarty->assign('username', $_SESSION['username']); }

// Script handler class
class ScriptHandler{
    public $connectionHandle;
    protected $id;
    public $dataArray;
    function __construct($id){
        $this->connectionHandle = $GLOBALS['connectionHandle'];
        $id = int($id);
        $this->id = $id;
        $query = $this->connectionHandle->query("SELECT * FROM repo_entries WHERE id='$id");
        if($query===false || $query->num_rows!==1){
            return false;
        }else{
            $this->dataArray = $query->fetch_assoc;
        }
    }
    function getAuthor(){
        return $this->dataArray['author'];
    }
    function getLikes(){
        return $this->dataArray['likes'];
    }
    function flag($username){
        $id = $this->id;
        $username = htmlspecialchars($username);
        $this->connectionHandle->query("INSERT INTO repo_flags (id, author, type, scriptID) VALUES ('NULL', '$username', '1', '$id')");
    }
}

// This is just on git.
include('password.php');
$connectionHandle = new mysqli('localhost', 'repo', $password, 'ScriptRepo');
include('assets/bcrypt.php');
function isValidLogin($user, $password){
    $bCrypt = new Bcrypt(12);
    $username = htmlspecialchars($user);
    $result = $GLOBALS['connectionHandle']->query("SELECT * FROM repo_users WHERE username='$username'");
    $row = $result->fetch_assoc();
    if($bCrypt->verify($password, $row['password'])){ return true; }else{ return false; }
}
function isActiveUser($user){
    $username = htmlspecialchars($user);
    $result = $GLOBALS['connectionHandle']->query("SELECT * FROM repo_users WHERE username='$username'");
    $row = $result->fetch_assoc();
    if($row['status']==1){ return true; }else{ return false; }
}
$bCrypt = new Bcrypt(12);

#$query2 = $connectionHandle->query("SELECT * FROM repo_flags");
//if($query2->num_rows>0){ $smarty->assign('adminNeeded', true); }

switch(strtolower($path[0])){
    case 'login':
        $smarty->assign('activePage', 'login');
        $smarty->assign('loginError', false);
        $smarty->assign('loginMessage', false);
        $smarty->assign('passwordError', false);
        $smarty->assign('userError', false);
        $smarty->assign('loginInfo', false);
        $smarty->assign('username', false);
        if(isset($_SESSION['loginInfo'])){
            $smarty->assign('loginInfo', $_SESSION['loginInfo']);
            unset($_SESSION['loginInfo']);
        }
        if(isset($_SESSION['loginMessage'])){
            $smarty->assign('loginMessage', $_SESSION['loginMessage']);
            unset($_SESSION['loginMessage']);
        }
        if(isset($_POST['loginForm'])){
            // Checks
            if($_POST['username']=="" || $_POST['password']==""){
                $smarty->assign('username', $_POST['username']);
                $smarty->assign('loginError', 'You must enter both a username and password.');
                $smarty->assign('userError', true);
                $smarty->assign('passwordError', true);
                $output = 'login.tpl';
            }elseif(!isValidLogin($_POST['username'], $_POST['password'])){
                $smarty->assign('username', $_POST['username']);
                $smarty->assign('loginError', 'Invalid username or password!');
                $smarty->assign('passwordError', true);
                $output = 'login.tpl';
            }elseif(!isActiveUser($_POST['username'])){
                $smarty->assign('username', $_POST['username']);
                $smarty->assign('loginError', 'You must activate your email before you can log in! <a href="http://scripts.citizensnpcs.com/resendConfirmation">Resend confirmation email.</a>');
                $_SESSION['attemptedUsername'] = htmlspecialchars($_POST['username']);
                $output = 'login.tpl';
            }else{
                // Login
                $_SESSION['loggedIn'] = true;
                $_SESSION['username'] = $_POST['username'];
                $query = $connectionHandle->query("SELECT * FROM repo_users WHERE username='".$_POST['username']."'");
                if($query!==false){
                    $row = $query->fetch_assoc();
                    if($row['staff']==1){ $_SESSION['admin'] = true; }else{ $_SESSION['admin'] = false; } }
                header('Location: http://scripts.citizensnpcs.com/');
                exit;
            }
        }elseif($_SESSION['loggedIn']){
            // Are they already logged in?
            header('Location: http://scripts.citizensnpcs.com/user/'.$_SESSION['username']);
            exit;
        }else{
            $output = 'login.tpl';
        }
        break;
    case 'settings':
        if(!$_SESSION['loggedIn']){
            $_SESSION['loginInfo'] = 'You must be logged in to change settings!';
            header('Location: http://scripts.citizensnpcs.com/login');
            exit;
        }
        $smarty->assign('successMessage', false);
        $currentTimezone = getCurrentTimeZone($_SESSION['username']);
        $smarty->assign('timezones', getTimeZoneOptions($currentTimezone));
        if(isset($_POST['Save'])){
            // Handle the update.
            if($_POST['timezone']!=$currentTimezone){
                // They updated the timezone. Make the changes.
                $newTimezone = htmlspecialchars($_POST['timezone']);
                $connectionHandle->query("UPDATE repo_users SET timezone='$newTimezone' WHERE username='".$_SESSION['username']."'");
                $currentTimezone = $newTimezone;
            }
            $smarty->assign('successMessage', "Successfully updated your settings.");
        }
        $smarty->assign('timezones', getTimeZoneOptions($currentTimezone));
        $smarty->assign('activePage', 'settings');
        $output = 'settings.tpl';
        break;
    case 'logout':
        session_destroy();
        session_start();
        $_SESSION['loginMessage'] = 'You have been successfully logged out.';
        header('Location: http://scripts.citizensnpcs.com/login');
        exit;
        break;
    case 'resendConfirmation':
        $query = $connectionHandle->query("SELECT * FROM repo_users WHERE username='".$_SESSION['attemptedUsername']."'");
        $row = $query->fetch_assoc;
        mail($email, "Please verify your registration at Denizen Script Repo.", "Someone, probably you, registered with the username $user on the Denizen Script Repo.
                        Before you can begin using the site, you must first confirm your account by clicking this link:
                        http://scripts.citizensnpcs.com/verify/$user/$confirmationCode/
                        
                        Thanks,
                        ~Administration");
        $_SESSION['loginMessage'] = 'Confirmation email successfully sent to '.$row['email'].'!';
        header('Location: http://scripts.citizensnpcs.com/login');
        $output = 'login.tpl';
        break;
    case 'register':
        $ayah = new AYAH($publisherKey, $scoringKey);
        $mailer = new Mailer(true);
        $smarty->assign('activePage', false);
        $smarty->assign('username', false);
        $smarty->assign('email', false);
        $smarty->assign('registerError', false);
        $smarty->assign('usernameError', false);
        $smarty->assign('emailError', false);
        $smarty->assign('passwordError', false);
        $smarty->assign('ayahError', false);
        $smarty->assign('ayah', $ayah->getPublisherHTML());
        if(isset($_POST['registerForm'])){
            $email = htmlentities($_POST['email']);
            $emailQuery = $connectionHandle->query("SELECT * FROM repo_users WHERE email='$email'");
            $user = htmlentities($_POST['username']);
            $userQuery = $connectionHandle->query("SELECT * FROM repo_users WHERE user='$user'");
            // Checks
            if(strlen($_POST['password'])<5){
                // Make sure the password is 5 characters long
                $smarty->assign('registerError', 'Password must be more than 5 characters!');
                $smarty->assign('username', $_POST['username']);
                $smarty->assign('email', $_POST['email']);
                $smarty->assign('passwordError', true);
                $output = 'register.tpl';
            }elseif($_POST['password']!==$_POST['passwordConfirm']){
                // Make sure the passwords match
                $smarty->assign('registerError', 'Passwords do not match!');
                $smarty->assign('username', $_POST['username']);
                $smarty->assign('email', $_POST['email']);
                $smarty->assign('passwordError', true);
                $output = 'register.tpl';
            }elseif(strlen($_POST['username'])<3){
                $smarty->assign('registerError', 'Username must be at least 3 characters!');
                $smarty->assign('username', $_POST['username']);
                $smarty->assign('email', $_POST['email']);
                $smarty->assign('usernameError', true);
                $output = 'register.tpl';
            }elseif(!validEmail($_POST['email'])){
                // Make sure the email address is a valid email address
                $smarty->assign('username', $_POST['username']);
                $smarty->assign('email', $_POST['email']);
                $smarty->assign('emailError', true);
                $smarty->assign('registerError', 'Invalid email address!');
                $output = 'register.tpl';
            }elseif($emailQuery->num_rows>0){
                // Make sure the email address isn't being used
                $smarty->assign('username', $_POST['username']);
                $smarty->assign('email', $_POST['email']);
                $smarty->assign('emailError', true);
                $smarty->assign('registerError', 'Email already in use!');
                $output = 'register.tpl';
            }elseif($userQuery->num_rows===0){
                // Make sure the username isn't taken
                $smarty->assign('username', $_POST['username']);
                $smarty->assign('email', $_POST['email']);
                $smarty->assign('userError', true);
                $smarty->assign('registerError', 'Username already in use!');
                $output = 'register.tpl';
            }elseif(!$ayah->scoreResult()){
                // Make sure the AYAH was correct
                $smarty->assign('username', $_POST['username']);
                $smarty->assign('email', $_POST['email']);
                $smarty->assign('ayahError', true);
                $smarty->assign('registerError', "The AreYouAHuman game wasn't completed properly. Please try it again.");
                $output = 'register.tpl';
            }else{
                // Register
                $bCrypt = new Bcrypt(12);
                $pass = $bCrypt->hash($_POST['password']);
                $connectionHandle->query("INSERT INTO repo_users (id, username, password, email, timezone, status, staff) VALUES ('NULL', '$user', '$pass', '$email', 'America/New_York', '0', false)") or die("MYSQL ERROR!");
                // Send them their confirmation email, too.
                $confirmationCode = md5($user);
                $mailer->Subject = "Please verify your registrations at Citizens Script Repo.";
                $mailer->Body = "Someone, probably you, registered with the username $user on the Citizens Script Repo.\nBefore you can begin using the site, you must first confirm your account by clicking on this link:
                        http://scripts.citizensnpcs.com/verify/$user/$confirmationCode/\nThanks,\n~Administration";
                $mailer->AddAddress($email, $user);
                header('Location: http://scripts.citizensnpcs.com/login');
                $_SESSION['loginMessage'] = 'You have now been registered, but must confirm your email before you can login.';
                $_SESSION['loginError'] = $mailer->Send();
                exit;
            }
        }else{
            $output = 'register.tpl';
        }
        break;
    case 'post':
        $smarty->assign('postError', false);
        $smarty->assign('scriptError', false);
        $smarty->assign('scriptCode', false);
        $smarty->assign('description', false);
        $smarty->assign('descriptionError', false);
        $smarty->assign('typeError', false);
        $smarty->assign('tagError', false);
        $smarty->assign('tags', false);
        if(!$_SESSION['loggedIn']){
                    $_SESSION['loginInfo'] = 'You must be logged in to post new scripts!';
                    header('Location: http://scripts.citizensnpcs.com/login');
                    exit;
                }
        if(isset($_POST['SubmitScript'])){
            var_dump($_POST);
            // Run some checks, make sure the data is good.
            $tagsRaw = explode(',', $_POST['tags']);
            $tags = array();
            foreach($tagsRaw as $tag){
                if($tag!=""){ array_push($tags, trim($tag)); }
            }
            var_dump($tags);
            if($_POST['Description']==""){
                // Description is empty.
                $smarty->assign('postError', "Description must not be empty!");
                $smarty->assign('descriptionError', true);
            }elseif($_POST['scriptCode']==""){
                // Script code is empty
                $smarty->assign('postError', "Code must not be empty!");
                $smarty->assign('scriptError', true);
            }elseif($_POST['typeOfScript']=="None"){
                // No type has been selected!
                $smarty->assign('postError', "Script type must be selected!");
                $smarty->assign('typeError', true);
            }elseif(count($tags)==0){
                $smarty->assign('postError', "You must enter at least one tag!");
                $smarty->assign('tagError', true);
            }else{
                $typeOfScript = intval($_POST['typeOfScript']);
                $scriptCode = htmlspecialchars($_POST['scriptCode']);
                $description = htmlspecialchars($_POST['Description']);
            }
            if(isset($_POST['scriptCode']) && $_POST['scriptCode']!=""){ $smarty->assign('scriptCode', $_POST['scriptCode']); }
            if(isset($_POST['Description']) && $_POST['Description']!=""){ $smarty->assign('description', $_POST['Description']); }
            if(isset($_POST['tags']) && $_POST['tags']!=""){ $smarty->assign('tags', $_POST['tags']); }
        }
        $smarty->assign('activePage', 'post');
        $output = 'post.tpl';
        break;
    case 'verify':
        $user = htmlspecialchars($path[2]);
        $query = $connectionHandle->query("SELECT * FROM repo_users WHERE username='$user' AND status=0");
        if(!isset($path[2]) || !isset($path[3]) || $path[2]!=md5($path[3]) || $query->num_rows===0){
            // Something's wrong.
            header('Location: http://scripts.citizensnpcs.com/');
            exit;
        }else{
            // Verify user
            $connectionHandle->query("UPDATE repo_users SET status=1 WHERE username='$user'");
            $_SESSION['loginMessage'] = 'You have successfully confirmed your email. You may now log in.';
            header('Location: http://scripts.citizensnpcs.com/login');
            exit;
        }
        break;
    case 'edit':
        break;
    case 'search':
        $smarty->assign('activePage', false);
        $query = htmlspecialchars(urldecode($path[1]));
        $searchSettings = array($path[2], $path[3], $path[4], $path[5]);
        $smarty->assign('searchQuery', $query);
        $smarty->assign('searchSettings', $searchSettings);
        $output = 'result.tpl';
        break;
    case 'admin':
        $query = $connectionHandle->query("SELECT * FROM repo_users WHERE username=".$_SESSION['username']." AND staff=1");
        if(!$_SESSION['loggedIn'] || $query->num_rows!==1){
            header('Location: http://scripts.citizensnpcs.com/login');
            $_SESSION['loginInfo'] = 'You must be logged in to do that!';
            exit;
        }
        $smarty->assign('activePage', 'admin');
        $output = 'admin.tpl';
        break;
    case 'support':
        $smarty->assign('activePage', false);
        $output = 'support.tpl';
        break;
    case 'list':
        $smarty->assign('activePage', 'list');
        $queryListing = $connectionHandle->query("SELECT * FROM repo_entries WHERE privacy=1");
        $numberPerPage = 20;
        $pageNumber = 1;
        $resultPages = array(1, 2, 3, 4, 5);
        if(isset($path[1])){
            $pageNumber = intval($path[1]);
            if(isset($path[2])){ $numberPerPage = intval($path[2]); }
        }
        if($queryListing!=false){
            $numberOfPages = ceil($queryListing->num_rows/$numberPerPage);
            $resultData = getResults($queryListing, $numberPerPage, $pageNumber);
        }
        if($pageNumber+2>$numberOfPages){
            $limit = $numberOfPages;
            $start = $pageNumber-(4-($numberOfPages-$pageNumber));
        }else{
            $limit = $pageNumber+2;
            $start = $pageNumber-2;
        }
        if($pageNumber<3){
            $resultPages = array(1, 2, 3, 4, 5);
        }else{
            $resultPages = range($start, $limit);
        }
        $smarty->assign('resultPageNumber', $pageNumber);
        $smarty->assign('resultsPerPage', $numberPerPage);
        $smarty->assign('resultPages', $resultPages);
        $output = 'list.tpl';
        break;
    case 'view':
        $pubID = addslashes(strtolower($path[1]));
        $query = $connectionHandle->query("SELECT * FROM repo_entries WHERE pubID='$pubID'");
        if($query->num_rows==0 && false){
            $output = '404.tpl';
        }else{
            // $dataToUse gets taken by view.php and turned into the main page.
            $data = $query->fetch_assoc();
            $smarty->assign('dataToUse', $data);
            $geshi = new GeSHi($data[''], 'php');
            $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);
            $smarty->assign('code', $geshi->parse_code());
            $newviews = $data['views']+1;
            $connectionHandle->query("UPDATE repo_entries SET views='$newviews' WHERE pubID='$pubID'");
            $smarty->assign('activePage', 'view');
            $output = 'view.tpl';
        }
        break;
    case 'user':
        if(!$_SESSION['loggedIn']){
            $_SESSION['loginInfo'] = 'You must be logged in to view user profiles!';
            header('Location: http://scripts.citizensnpcs.com/login');
            exit;
        }
        break;
    case 'recover':
        if(isset($_POST['recover'])){
            $_SESSION['loginInfo'] = 'A new password has been sent to your email address!';
            header('Location: http://scripts.citizensnpcs.com/login');
            exit;
        }
        $output = 'recover.tpl';
        break;
    case '':
    case 'index':
    case 'home':
        $smarty->assign('activePage', 'home');
        $output = 'home.tpl';
        break;
    default:
        $output = '404.tpl';
        break;
}

/*
 * If the page is supposed to read raw data, echo it and die.
 */
if(!isset($output)){ $smarty->assign('output', '404.tpl'); }else{ $smarty->assign('output', $output); }
$smarty->display('index.tpl');
?>