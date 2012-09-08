<?php
ini_set('display_errors', 'On');
session_start();
if(!isset($_SESSION['loggedIn'])){ $_SESSION['loggedIn'] = false; }
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');

// Get the arguments from the url
$_SERVER['REQUEST_URI_PATH'] = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
$args = explode('/', trim($_SERVER['REQUEST_URI_PATH'], '/'));

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

// Smarty
include('assets/Smarty/Smarty.class.php');
$smarty = new Smarty;
$smarty->setTemplateDir('/usr/share/nginx/www/scripts/assets/templates');
$smarty->setCompileDir('/usr/share/nginx/www/scripts/assets/Smarty/templates_c');
$smarty->setCacheDir('/usr/share/nginx/www/scripts/assets/Smarty/cache');
$smarty->setConfigDir('/usr/share/nginx/www/scripts/assets/Smarty/configs');
$smarty->assign('loggedIn', $_SESSION['loggedIn']);
if($_SESSION['loggedIn']){ $smarty->assign('username', $_SESSION['username']); }

// This is just on git.
include('password.php');
$connectionHandle = new mysqli('localhost', 'repo', $password, 'ScriptRepo');
include('assets/bcrypt.php');
function isValidLogin($user, $password){
    $bCrypt = new Bcrypt(12);
    $username = htmlspecialchars($user);
    $result = $GLOBALS['connectionHandle']->query("SELECT * FROM repo_users WHERE username='$username'");
    $row = $result->fetch_assoc();
    return $bCrypt->verify($password, $row['password']);
    return false;
}
$bCrypt = new Bcrypt(12);
switch(strtolower($args[0])){
    case 'login':
        $smarty->assign('loginError', false);
        $smarty->assign('registerFinished', false);
        $smarty->assign('passwordError', false);
        $smarty->assign('userError', false);
        if(isset($_SESSION['justRegistered'])){
            unset($_SESSION['justRegistered']);
            $smarty->assign('registerFinished', 'You have now been registered and can log in. You will not be able to post until you verify your email.');
        }
        if(isset($_POST['login'])){
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
            }else{
                // Login
                $_SESSION['loggedIn'] = true;
                $_SESSION['username'] = $_POST['username'];
                header('Location: http://scripts.citizensnpcs.com/');
                exit;
            }
        }elseif($_SESSION['loggedIn']){
            // Are they already logged in?
            header('Location: http://scripts.citizensnpcs.com/user/'.$_SESSION['username'].'/');
            exit;
        }else{
            $output = 'login.tpl';
        }
        break;
    case 'logout':
        break;
    case 'register':
        $ayah = new AYAH($publisherKey, $scoringKey);
        $smarty->assign('registerError', false);
        $smarty->assign('usernameError', false);
        $smarty->assign('emailError', false);
        $smarty->assign('passwordError', false);
        $smarty->assign('ayahError', false);
        $smarty->assign('ayah', $ayah->getPublisherHTML());
        if(isset($_POST['register'])){
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
                $connectionHandle->query("INSERT INTO repo_users (id, username, password, email, status, staff) VALUES ('NULL', '$user', '$pass', '$email', '0', false)") or die("MYSQL ERROR!");
                // Send them their confirmation email, too.
                $confirmationCode = md5($user);
                mail($email, "Please verify your registration at Denizen Script Repo.", "Someone, probably you, registered with the username $user on the Denizen Script Repo.
                        Before you can begin using the site, you must first confirm your account by clicking this link:
                        http://scripts.citizensnpcs.com/verify/$user/$confirmationCode/
                        
                        Thanks,
                        ~Administration");
                header('Location: http://scripts.citizensnpcs.com/login');
                $_SESSION['justRegistered'] = true;
                exit;
            }
        }else{
            $output = 'register.tpl';
        }
        break;
    case 'post':
        break;
    case 'verify':
        $user = htmlspecialchars($args[2]);
        $query = $connectionHandle->query("SELECT * FROM repo_users WHERE username='$user' AND confirmed=false");
        if(!isset($args[2]) || !isset($args[3]) || $args[2]!=md5($args[3]) || $query->num_rows===0){
            // Something's wrong.
            header('Location: http://scripts.citizensnpcs.com/');
            exit;
        }else{
            // Verify user
            $connectionHandle->query("UPDATE repo_users SET 'confirmed'=true WHERE 'username'='$user'");
            $smarty->assign('registerFinished', 'You have successfully confirmed your email. You may now log in.');
            $output = 'login.tpl';
        }
        break;
    case 'edit':
        break;
    case 'search':
        break;
    case 'list':
        break;
    case 'view':
        $pubID = addslashes(strtolower($args[1]));
        $query = $connectionHandle->query("SELECT * FROM repo_entries WHERE pubID='$pubID'");
        if($query->num_rows==0){ $output='unknownPage.tpl'; }else{
            // $dataToUse gets taken by view.php and turned into the main page.
            $smarty->assign('dataToUse', $query->fetch_assoc());
            $smarty->assign('activePage', 'view');
            $output = 'view.tpl';
        }
        break;
    case 'user':
        break;
    case 'admin':
        break;
    case '':
        $smarty->assign('activePage', 'home');
        $output = 'home.tpl';
        break;
    default:
        $output = '404.tpl';
        break;
}


/*
 * If the page is supposed to handle cookies, write them.
 */


/*
 * If the page is supposed to read raw data, echo it and die.
 */
if(!isset($output)){ $smarty->assign('output', '404.tpl'); }else{ $smarty->assign('output', $output); }
$smarty->display('index.tpl');
?>