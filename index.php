<?php
ini_set('display_errors', '1'); // Error reporting on.
session_start(); // Create a session.
date_default_timezone_set('America/New_York'); // Set the timezone.

// Include external classes.
require_once('assets/phpmailer/mail.php');
require_once('assets/ayah.php');
require_once('assets/Smarty/Smarty.class.php');
require_once('password.php');
require_once('assets/bcrypt.php');
require_once('assets/scriptrepo.class.php');
require_once('assets/logger.class.php');

// Initialize the ScriptRepo.
new ScriptRepo();
?>