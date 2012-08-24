<?php
session_start();

// Get the arguments from the url
$_SERVER['REQUEST_URI_PATH'] = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
$args = explode('/', trim($_SERVER['REQUEST_URI_PATH'], '/'));

// 
/**
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
function alphaID($in, $to_num = false, $pad_up = false)
{
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

// Smarty
include('assets/Smarty/Smarty.class.php');
$smarty = new Smarty;
$smarty->setTemplateDir('/var/www/computercraft/repo/assets/templates');
$smarty->setCompileDir('/var/www/computercraft/repo/assets/Smarty/templates_c');
$smarty->setCacheDir('/var/www/computercraft/repo/assets/Smarty/cache');
$smarty->setConfigDir('/var/www/computercraft/repo/assets/Smarty/configs');
$smarty->assign($loginStatus, 1);

// This is just on git.
include('password.php');
$connectionHandle = new mysqli('localhost', 'ComputerCraft', $password, 'ComputerCraft');
include('assets/bcrypt.php');
function isValidLogin($user, $password){
    $bCrypt = new Bcrypt(24);
    $username = htmlspecialchars($user);
    $result = $connectionHandle->query("SELECT * FROM repo_users WHERE name='$username'");
    $row = $result->fetch_assoc();
    return $bCrypt->verify($password, $row['pass']);
}


switch(strtolower($args[0])){
    case 'login':
        break;
    case 'logout':
        break;
    case 'register':
        break;
    case 'post':
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
        if($query->num_rows===0){ $output='assets/pages/404view.php'; }else{
            // $dataToUse gets taken by view.php and turned into the main page.
            $dataToUse = $query->fetch_assoc();
            $output = 'assets/pages/view.php';
        }
        break;
    case 'user':
        break;
    case 'admin':
        break;
    case '':
        break;
    default:
        break;
}


/*
 * If the page is supposed to handle cookies, write them.
 */


/*
 * If the page is supposed to read raw data, echo it and die.
 */
if($page=='raw'){
    include('pages/raw.php');
    exit;
}
if(!isset($output)){ $smarty->assign('output', '404.tpl'); }else{ $smarty->assign('output', $output); }
$smarty->display('index.tpl');
?>