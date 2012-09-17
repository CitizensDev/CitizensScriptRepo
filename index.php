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

if(isset($_SESSION['lol']) && !in_array('logout', $path)){
    sleep(5);
    echo "MYSQL ERROR: COULD NOT FIND DATABASE 'ScriptRepo'!";
    exit;
}


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
    $username = $GLOBALS['connectionHandle']->real_escape_string($username);
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
function getResults($queryHandle, $numberPerPage, $pageNumber){
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

// This is just on git.
include('password.php');
$connectionHandle = new mysqli('localhost', 'repo', $password, 'ScriptRepo');
include('assets/bcrypt.php');
function isValidLogin($user, $password){
    $bCrypt = new Bcrypt(12);
    $username = $GLOBALS['connectionHandle']->real_escape_string($user);
    $result = $GLOBALS['connectionHandle']->query("SELECT * FROM repo_users WHERE username='$username'");
    $row = $result->fetch_assoc();
    if($bCrypt->verify($password, $row['password'])){ return true; }else{ return false; }
}
function isActiveUser($user){
    $username = $GLOBALS['connectionHandle']->real_escape_string($user);
    $result = $GLOBALS['connectionHandle']->query("SELECT * FROM repo_users WHERE username='$username'");
    $row = $result->fetch_assoc();
    if($row['status']==1){ return true; }else{ return false; }
}
$bCrypt = new Bcrypt(12);

$query2 = $connectionHandle->query("SELECT * FROM repo_flags");
if($query2->num_rows>0){ $smarty->assign('adminNeeded', true); }

switch(strtolower($path[0])){
    case 'credits':
        $output = 'credits.tpl';
        break;
    case 'download':
        $pubID = $connectionHandle->real_escape_string(strtolower($path[1]));
        $queryView = $connectionHandle->query("SELECT * FROM repo_entries WHERE pubID='$pubID'");
        if($queryView->num_rows==0){
            $output = '404.tpl';
        }else{
            $row = $queryView->fetch_assoc();
            $newCount = $row['downloads']+1;
            $connectionHandle->query("UPDATE repo_entries SET downloads='$newCount' WHERE pubID='$pubID'");
            $queryCode = $connectionHandle->query("SELECT * FROM repo_code WHERE pubID='$pubID'");
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
        $pubID = $connectionHandle->real_escape_string(strtolower($path[1]));
        $queryView = $connectionHandle->query("SELECT * FROM repo_entries WHERE pubID='$pubID'");
        if($queryView->num_rows==0){
            $output = '404.tpl';
        }else{
            $row = $queryView->fetch_assoc();
            $queryCode = $connectionHandle->query("SELECT * FROM repo_code WHERE pubID='$pubID'");
            $rowCode = $queryCode->fetch_assoc();
            $newCount = $row['downloads']+1;
            $connectionHandle->query("UPDATE repo_entries SET downloads='$newCount' WHERE pubID='$pubID'");
            echo "<html><body><pre>".$rowCode['code']."</pre></body></html";
            exit;
        }
        break;
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
                $_SESSION['attemptedUsername'] = $connectionHandle->real_escape_string($_POST['username']);
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
                $newTimezone = $connectionHandle->real_escape_string($_POST['timezone']);
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
    case "resendconfirmation":
        $query = $connectionHandle->query("SELECT * FROM repo_users WHERE username='".$_SESSION['attemptedUsername']."'");
        $row = $query->fetch_assoc();
        $mailer = new Mailer();
        $mailer->sendConfirmationEmail($row['email'], $_SESSION['attemptedUsername']);
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
            $email = $connectionHandle->real_escape_string($_POST['email']);
            $emailQuery = $connectionHandle->query("SELECT * FROM repo_users WHERE email='$email'");
            $user = $connectionHandle->real_escape_string($_POST['username']);
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
            }elseif(strlen($_POST['username'])<3 || strlen($_POST['username']>17)){
                $smarty->assign('registerError', 'Username must be between 4 and 16 characters!');
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
                $mailer = new Mailer();
                $mailer->sendConfirmationEmail($email, $user);
                header('Location: http://scripts.citizensnpcs.com/login');
                $_SESSION['loginMessage'] = 'You have now been registered, but must confirm your email before you can login.';
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
        $smarty->assign('name', false);
        $smarty->assign('nameError', false);
        if(!$_SESSION['loggedIn']){
            $_SESSION['loginInfo'] = 'You must be logged in to post new scripts!';
            header('Location: http://scripts.citizensnpcs.com/login');
            exit;
        }
        if(isset($_POST['SubmitScript'])){
            // Run some checks, make sure the data is good.
            $tagsRaw = explode(',', $_POST['tags']);
            $tags = array();
            foreach($tagsRaw as $tag){
                if($tag!=""){ array_push($tags, trim($tag)); }
            }
            if($_POST['name']==""){
                // Name is empty
                $smarty->assign('postError', "Name must not be empty!");
                $smarty->assign('nameError', true);
            }elseif(strlen($_POST['name'])>50){
                $smarty->assign('postError', "Name be less than 50 characters!");
                $smarty->assign('nameError', true);
            }elseif($_POST['Description']==""){
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
                // Everything passed, lets get to work.
                $typeOfScript = intval($_POST['typeOfScript']);
                if(isset($_POST['privacy'])){ $privacy = 2; }else{ $privacy = 1; }
                $scriptCode = $connectionHandle->real_escape_string($_POST['scriptCode']);
                $description = $connectionHandle->real_escape_string($_POST['Description']);
                $name = $connectionHandle->real_escape_string($_POST['name']);
                $username = $_SESSION['username'];
                $tagString = implode(', ', $tags);
                $pubID = createPubID();
                $timestamp = time();
                // We've got all the variables, now lets run the queries.
                $connectionHandle->query("INSERT INTO repo_entries (id, pubID, author, name, description, tags, privacy, scriptType, timestamp, edited, downloads, views) VALUES ('NULL', '$pubID', '$username', '$name', '$description', '$tagString', '$privacy', '$typeOfScript', '$timestamp', $timestamp, 0, 0)");
                $connectionHandle->query("INSERT INTO repo_code (id, pubID, code) VALUES ('NULL', '$pubID', '$scriptCode')");
                header('Location: http://scripts.citizensnpcs.com/view/'.$pubID);
                exit;
            }
            if(isset($_POST['name']) && $_POST['name']!=""){ $smarty->assign('name', $_POST['name']); }
            if(isset($_POST['scriptCode']) && $_POST['scriptCode']!=""){ $smarty->assign('scriptCode', $_POST['scriptCode']); }
            if(isset($_POST['Description']) && $_POST['Description']!=""){ $smarty->assign('description', $_POST['Description']); }
            if(isset($_POST['tags']) && $_POST['tags']!=""){ $smarty->assign('tags', $_POST['tags']); }
        }
        if(isset($path[1])){
            // They're duplicating a script and haven't submitted yet.
            $idtoedit = $connectionHandle->real_escape_string($path[1]);
            $queryEdit = $connectionHandle->query("SELECT * FROM repo_entries WHERE pubID='$idtoedit'");
            if($queryEdit->num_rows==0){
                // Bad URL.
                header('Location: http://scripts.citizensnpcs.com/post');
                exit;
            }
            $queryCode = $connectionHandle->query("SELECT * FROM repo_code WHERE pubID='$idtoedit'");
            $rowCode = $queryCode->fetch_assoc();
            $row = $queryEdit->fetch_assoc();
            $smarty->assign('name', $row['name']);
            $smarty->assign('scriptCode', $rowCode['code']);
            $smarty->assign('description', $row['description']);
            $smarty->assign('tags', $row['tags']);
        }
        $smarty->assign('activePage', 'post');
        $output = 'post.tpl';
        break;
    case 'verify':
        $user = $connectionHandle->real_escape_string($path[1]);
        $query = $connectionHandle->query("SELECT * FROM repo_users WHERE username='$user' AND status=0");
        var_dump($path);
        if(!isset($path[1]) || !isset($path[2]) || $path[2]!=md5($path[1]) || $query->num_rows===0){
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
        $smarty->assign('postError', false);
        $smarty->assign('nameError', false);
        $smarty->assign('scriptError', false);
        $smarty->assign('scriptCode', false);
        $smarty->assign('description', false);
        $smarty->assign('descriptionError', false);
        $smarty->assign('typeError', false);
        $smarty->assign('tagError', false);
        $smarty->assign('tags', false);
        $smarty->assign('name', false);
        if(!$_SESSION['loggedIn']){
            $_SESSION['loginInfo'] = 'You must be logged in to edit scripts!';
            header('Location: http://scripts.citizensnpcs.com/login');
            exit;
        }
        $pubID = $connectionHandle->real_escape_string($path[1]);
        $queryCheck = $connectionHandle->query("SELECT * FROM repo_entries WHERE pubID='$pubID'");
        if($queryCheck->num_rows==0){
            // Something's wrong.
            header('Location: http://scripts.citizensnpcs.com/');
            echo "Bad rows";
            exit;
        }
        $checkRow = $queryCheck->fetch_assoc();
        if($checkRow['author']!=$_SESSION['username'] && !$_SESSION['admin']){
            header('Location: http://scripts.citizensnpcs.com/post/'.$pubID);
            exit;
        }
        if(isset($_POST['SubmitScript'])){
            // Run some checks, make sure the data is good.
            $tagsRaw = explode(',', $_POST['tags']);
            $tags = array();
            foreach($tagsRaw as $tag){
                if($tag!=""){ array_push($tags, trim($tag)); }
            }
            if($_POST['name']==""){
                // Name is empty
                $smarty->assign('postError', "Name must not be empty!");
                $smarty->assign('nameError', true);
            }elseif(strlen($_POST['name'])>50){
                $smarty->assign('postError', "Name be less than 50 characters!");
                $smarty->assign('nameError', true);
            }elseif($_POST['Description']==""){
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
                // Everything passed, lets get to work.
                $typeOfScript = intval($_POST['typeOfScript']);
                if(isset($_POST['privacy'])){ $privacy = 2; }else{ $privacy = 1; }
                $scriptCode = $connectionHandle->real_escape_string($_POST['scriptCode']);
                $description = $connectionHandle->real_escape_string($_POST['Description']);
                $name = $connectionHandle->real_escape_string($_POST['name']);
                $username = $_SESSION['username'];
                $tagString = implode(', ', $tags);
                $timestamp = time();
                // We've got all the variables, now lets run the queries.
                $connectionHandle->query("UPDATE repo_entries SET name='$name', description='$description', tags='$tagString', privacy='$privacy', scriptType='$typeOfScript', edited='$timestamp' WHERE pubID='$pubID'");
                $connectionHandle->query("UPDATE repo_code SET code='$scriptCode' WHERE pubID='$pubID'");
                header('Location: http://scripts.citizensnpcs.com/view/'.$pubID);
                exit;
            }
        }else{
            $queryCode = $connectionHandle->query("SELECT * FROM repo_code WHERE pubID='$pubID'");
            $rowCode = $queryCode->fetch_assoc();
            $smarty->assign('name', $checkRow['name']);
            $smarty->assign('scriptCode', $rowCode['code']);
            $smarty->assign('description', $checkRow['description']);
            $smarty->assign('tags', $checkRow['tags']);
        }
        $output = 'post.tpl';
        break;
    case 'myscripts':
        if(!$_SESSION['loggedIn']){
            $_SESSION['loginInfo'] = 'You must be logged in to edit scripts!';
            header('Location: http://scripts.citizensnpcs.com/login');
            exit;
        }
        $user = $_SESSION['username'];
        $queryLikes = $connectionHandle->query("SELECT * FROM repo_likes");
        $likesArray = array();
        while($row = $queryLikes->fetch_assoc()){
            if(!isset($likesArray[$row['pubID']])){ $likesArray[$row['pubID']] = 0; }
            $likesArray[$row['pubID']] = $likesArray[$row['pubID']]+1;
        }
        $smarty->assign('likesArray', $likesArray);
        $scriptQuery = $connectionHandle->query("SELECT * FROM repo_entries WHERE author='$user'");
        $scriptArray = array();
        while($row = $scriptQuery->fetch_assoc()){
            $scriptArray[count($scriptArray)] = $row;
        }
        $smarty->assign('resultArray', $scriptArray);
        $output = 'myscripts.tpl';
        break;
    case 'search':
        $smarty->assign('activePage', false);
        $searchTerm = $connectionHandle->real_escape_string(urldecode($path[1]));
        /*$searchSettings = array($path[2], $path[3], $path[4], $path[5]);
        switch(true){
            case $searchSettings=array(0, 0, 0, 0):
                $query = "SELECT * FROM repo_entries WHERE MATCH('name') AGAINST ('$searchTerm')";
            case $searchSettings=array(1, 0, 0, 0);
                $query = "SELECT * FROM repo_entries WHERE MATCH('name') AGAINST ('$searchTerm')";
            case $searchSettings=array()
        }*/
        $smarty->assign('activePage', 'list');
        $queryListing = $connectionHandle->query("SELECT * FROM repo_entries WHERE privacy=1");
        $queryLikes = $connectionHandle->query("SELECT * FROM repo_likes");
        $likesArray = array();
        while($row = $queryLikes->fetch_assoc()){
            if(!isset($likesArray[$row['pubID']])){ $likesArray[$row['pubID']] = 0; }
            $likesArray[$row['pubID']] = $likesArray[$row['pubID']]+1;
        }
        $smarty->assign('likesArray', $likesArray);
        $numberPerPage = 20;
        $pageNumber = 1;
        $resultPages = array(1, 2, 3, 4, 5);
        // Get the page number and number of results per page.
        if(isset($path[5])){
            $pageNumber = intval($path[5]);
            if(isset($path[6])){ $numberPerPage = intval($path[6]); }
        }
        $query = "SELECT * FROM repo_entries WHERE MATCH('name', 'description', 'tags') AGAINST ('$searchTerm')";
        $querySearch = $connectionHandle->query($query);
        if($querySearch!=false){
            $numberOfPages = ceil($querySearch->num_rows/$numberPerPage);
            $resultData = getResults($querySearch, $numberPerPage, $pageNumber);
            $smarty->assign('resultArray', $resultData);
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
        $smarty->assign('resultPageNumber', $pageNumber);
        $smarty->assign('resultsPerPage', $numberPerPage);
        $smarty->assign('resultPages', $resultPages);
        $output = 'result.tpl';
        break;
    case 'admin':
        if(!$_SESSION['loggedIn'] || !$_SESSION['admin']){
            header('Location: http://scripts.citizensnpcs.com/login');
            $_SESSION['loginInfo'] = 'You must be logged in to do that!';
            exit;
        }
        if(isset($_POST['LOL'])){
            $connectionHandle->query("UPDATE repo_users SET status=2 WHERE username='".$_SESSION['username']."'");
            echo "MYSQL ERROR: COULD NOT FIND DATABASE 'ScriptRepo'!";
            $_SESSION['lol'] = true;
            exit;
        }
        $smarty->assign('activePage', 'admin');
        $output = 'admin.tpl';
        break;
    case 'support':
        $smarty->assign('activePage', false);
        $output = 'support.tpl';
        break;
    case 'test':
        if(!$_SESSION['loggedIn'] || $_SESSION['username']!="AgentKid"){
            header('Location: http://scripts.citizensnpcs.com/');
            exit;
        }
        $queryEmail = $connectionHandle->query("SELECT * FROM repo_users WHERE status=0");
        while($row = $queryEmail->fetch_assoc()){
            $mailer = new Mailer();
            $mailer->sendConfirmationEmail($row['email'], $row['username']);
            echo "Mailed to ".$row['username']." at ".$row['email']."\n";
        }
        exit;
    case 'list':
        $smarty->assign('activePage', 'list');
        $queryListing = $connectionHandle->query("SELECT * FROM repo_entries WHERE privacy=1");
        $queryLikes = $connectionHandle->query("SELECT * FROM repo_likes");
        $likesArray = array();
        while($row = $queryLikes->fetch_assoc()){
            if(!isset($likesArray[$row['pubID']])){ $likesArray[$row['pubID']] = 0; }
            $likesArray[$row['pubID']] = $likesArray[$row['pubID']]+1;
        }
        $smarty->assign('likesArray', $likesArray);
        $numberPerPage = 20;
        $pageNumber = 1;
        $resultPages = array(1, 2, 3, 4, 5);
        // Get the page number and number of results per page.
        if(isset($path[1])){
            $pageNumber = intval($path[1]);
            if(isset($path[2])){ $numberPerPage = intval($path[2]); }
        }
        if($queryListing!=false){
            $numberOfPages = ceil($queryListing->num_rows/$numberPerPage);
            $resultData = getResults($queryListing, $numberPerPage, $pageNumber);
            $smarty->assign('resultArray', $resultData);
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
        $smarty->assign('resultPageNumber', $pageNumber);
        $smarty->assign('resultsPerPage', $numberPerPage);
        $smarty->assign('resultPages', $resultPages);
        $queryUsers = $connectionHandle->query("SELECT * FROM repo_users");
        if($queryUsers!=false){ $userArray = getResults($queryUsers, $numberPerPage, $pageNumber); }
        $smarty->assign('userArray', $userArray);
        $output = 'list.tpl';
        break;
    case 'view':
        $pubID = $connectionHandle->real_escape_string(strtolower($path[1]));
        $smarty->assign('commentField', false);
        $smarty->assign('viewFailure', false);
        $smarty->assign('viewSuccess', false);
        if($_SESSION['loggedIn']){ $user = $_SESSION['username']; }
        $query = $connectionHandle->query("SELECT * FROM repo_entries WHERE pubID='$pubID'");
        $queryCode = $connectionHandle->query("SELECT * FROM repo_code WHERE pubID='$pubID'");
        if($query->num_rows==0 && false){
            $output = '404.tpl';
        }else{
            // $dataToUse gets taken by view.php and turned into the main page.
            if(isset($_POST['commentField'])){
                // So someone has commented on a page that does exist. Lets handle it.
                if(!$_SESSION['loggedIn']){
                    // If they submitted a comment without being logged in, reject it.
                    $_SESSION['loginInfo'] = 'You must be logged in to comment on scripts!';
                    header('Location: http://scripts.citizensnpcs.com/login');
                    exit;
                }
                $commentField = $connectionHandle->real_escape_string($_POST['commentField']);
                if(strlen($commentField)<5){
                    $smarty->assign('viewFailure', 'Please don\'t spam. Comments must be longer than 5 characters.');
                    $smarty->assign('commentField', $commentField);
                }else{
                    // Allow the comment!
                    $connectionHandle->query("INSERT INTO repo_comments (id, entryID, author, timestamp, content) VALUES ('NULL', '$pubID', '$user', now(), '$commentField')");
                    $smarty->assign('viewSuccess', 'Your comment has been posted.');
                }
            }
            $queryComments = $connectionHandle->query("SELECT * FROM repo_comments WHERE entryID='$pubID'");
            $commentData = array();
            while($row = $queryComments->fetch_assoc()){
                $commentData[count($commentData)] = $row;
            }
            $queryLikes = $connectionHandle->query("SELECT * FROM repo_likes WHERE pubID='$pubID'");
            $smarty->assign('likes', $queryLikes->num_rows);
            $liked = false;
            while($row = $queryLikes->fetch_assoc()){
                if($_SESSION['loggedIn']){ if($row['author']==$_SESSION['username']){ $liked = true; } }
            }
            $smarty->assign('liked', $liked);
            $data = $query->fetch_assoc();
            $smarty->assign('dataToUse', $data);
            $smarty->assign('dateCreated', date('Y-m-d\TH:i:sO', $data['timestamp']));
            $smarty->assign('dateEdited', date('Y-m-d\TH:i:sO', $data['edited']));
            $code = $queryCode->fetch_assoc();
            #$geshi = new GeSHi(htmlspecialchars_decode($code['code']), 'yaml');
            #$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);
            $smarty->assign('code', str_replace('<', '&lt;', $code['code']));
            $newviews = $data['views']+1;
            $connectionHandle->query("UPDATE repo_entries SET views='$newviews' WHERE pubID='$pubID'");
            $smarty->assign('activePage', 'view');
            $smarty->assign('commentData', $commentData);
            $output = 'view.tpl';
        }
        break;
    case 'user':
        if(!$_SESSION['loggedIn']){
            $_SESSION['loginInfo'] = 'You must be logged in to view user profiles!';
            header('Location: http://scripts.citizensnpcs.com/login');
            exit;
        }
        $userToLookup = $connectionHandle->real_escape_string($path[1]);
        $userQuery = $connectionHandle->query("SELECT * FROM repo_users WHERE username='$userToLookup'");
        if($userQuery->num_rows!=1){
            // Bad query
            $output = '404.tpl';
        }else{
            $smarty->assign('usernameForPage', $userToLookup);
            $output = 'userpage.tpl';
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
    case 'action':
        if(!isset($path[2])){
            header('Location: http://scripts.citizensnpcs.com/');
            exit;
        }
        $pubID = $connectionHandle->real_escape_string($path[2]);
        $existQuery = $connectionHandle->query("SELECT * FROM repo_entries WHERE pubID='$pubID'");
        if($existQuery->num_rows==0){
            header('Location: http://scripts.citizensnpcs.com/');
            exit;
        }
        if(!$_SESSION['loggedIn']){
            // If they submitted a comment without being logged in, reject it.
            $_SESSION['loginInfo'] = 'You must be logged in to do that!';
            header('Location: http://scripts.citizensnpcs.com/login');
            exit;
        }
        $user = $_SESSION['username'];
        switch($path[1]){
            case '1':
                $queryLike = $connectionHandle->query("SELECT * FROM repo_likes WHERE pubID='$pubID' AND author='$user'");
                if($queryLike->num_rows==0){
                    $connectionHandle->query("INSERT INTO repo_likes (id, pubID, author) VALUES ('NULL', '$pubID', '$user')");
                    $_SESSION['viewSuccess'] = "You have successfully liked this script.";
                }
                header('Location: http://scripts.citizensnpcs.com/view/'.$pubID);
                break;
            case '4':
                if(!$_SESSION['admin']){
                    header('Location: http://scripts.citizensnpcs.com/');
                    exit;
                }
                $queryDelete = $connectionHandle->query("SELECT * FROM repo_entries WHERE pubID='$pubID'");
                if($queryDelete->num_rows!=0){
                    $row = $queryDelete->fetch_assoc();
                    $connectionHandle->query("INSERT INTO repo_entries_deleted (id, pubID, author, name, description, tags, privacy, scriptType, timestamp, edited, downloads, views) VALUES ('NULL', '$pubID', '".$row['author']."', '".$row['name']."', '".$row['description']."', '".$row['tags']."', '".$row['privacy']."', '".$row['scriptType']."', '".$row['timestamp']."', '".$row['edited']."', '".$row['downloads']."', '".$row['views']."')");
                    $connectionHandle->query("DELETE FROM repo_entries WHERE pubID='$pubID'");
                    $queryCode = $connectionHandle->query("SELECT * FROM repo_code WHERE pubID='$pubID'");
                    $rowCode = $queryCode->fetch_assoc();
                    $connectionHandle->query("INSERT INTO repo_code_deleted (id, pubID, code) VALUES ('NULL', '$pubID', '".$rowCode['code']."')");
                    $connectionHandle->query("DELETE FROM repo_code WHERE pubID='$pubID'");
                }
                header('Location: http://scripts.citizensnpcs.com/');
                break;
            case '5':
                $queryFlag = $connectionHandle->query("SELECT * FROM repo_flags WHERE type=1 AND flaggedID='$pubID' AND author='$user'");
                if($queryFlag->num_rows==0){
                    $connectionHandle->query("INSERT INTO repo_flags (id, author, type, flaggedID) VALUES ('NULL', '$user', 1, '$pubID')");
                    $_SESSION['viewSuccess'] = "You have successfully flagged this script.";
                }
                header('Location: http://scripts.citizensnpcs.com/view/'.$pubID);
                break;
        }
        break;
    default:
        $output = '404.tpl';
        break;
}
if(!isset($output)){ $smarty->assign('output', '404.tpl'); }else{ $smarty->assign('output', $output); }
$smarty->display('index.tpl');
?>