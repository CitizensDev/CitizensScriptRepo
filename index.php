<?php
session_start();

// Get the arguments from the url
$_SERVER['REQUEST_URI_PATH'] = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
$args = explode('/', trim($_SERVER['REQUEST_URI_PATH'], '/'));

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
        $output = 'assets/pages/admin.php';
        break;
    case '':
        $output = 'assets/pages/index.php';
        break;
    default:
        $output = 'assets/pages/404.php';
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
$smarty->display('index.tpl');
?>