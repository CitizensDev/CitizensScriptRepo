<?php
ini_set('display_errors', '1');
session_start();
if(!isset($_SESSION['loggedIn'])){ $_SESSION['loggedIn'] = false; }
if(!isset($_SESSION['admin'])){ $_SESSION['admin'] = false; }
date_default_timezone_set('America/New_York');

// Get the arguments from the url
$_SERVER['REQUEST_URI_PATH'] = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
$path = explode('/', trim($_SERVER['REQUEST_URI_PATH'], '/'));
array_shift($_GET);

require_once('assets/phpmailer/mail.php');
require_once('assets/ayah.php');
require_once('assets/Smarty/Smarty.class.php');
require_once('password.php');
require_once('assets/bcrypt.php');
require_once('assets/scriptrepo.class.php');

$pageHandle = new ScriptRepo();
$pageHandle->initSmarty();
$pageHandle->webStuff();
$pageHandle->handlePage($path);
?>