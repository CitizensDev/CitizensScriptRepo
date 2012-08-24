<?php
var_dump(crc32('asdfasdf'));
session_start();
include('classes/main.class.php');
#if($_GET['d']!=8327962){ header('Location: http://computercraft.org/'); exit; }

$_SERVER['REQUEST_URI_PATH'] = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
$args = explode('/', trim($_SERVER['REQUEST_URI_PATH'], '/'));

include('password.php');
$connectionHandle = new mysqli('localhost', 'ComputerCraft', $password, 'ComputerCraft');

switch(strtolower($args[0])){
    case 'login':
        $output = "You are now logging in!";
        break;
    case 'logout':
        $output = "You are now logging out!";
        break;
    case 'register':
        $output = "You are now registering!";
        break;
    case 'post':
        $output = "You are now posting!";
        break;
    case 'edit':
        $output = "You are now editing!";
        break;
    case 'search':
        $output = "You are now searching!";
        break;
    case 'list':
        $output = "You are now listing!";
        break;
    case 'view':
        $connectionHandle = new mysqli();
        $data = 
        $output = "You are now viewing!";
        break;
    case 'user':
        $output = "You are now usering! :~)";
        break;
    case 'admin':
        $output = "You are now admining!";
        break;
    case '':
        $output = "Index!";
        break;
    default:
        $output = "Could not find a page by that name!";
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
?>
<html>
    <head>
        <title>ComputerCraft Program Repo</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="http://repo.computercraft.org/assets/css/bootstrap.min.css" type="text/css" media="screen" charset="utf-8">
        <link rel="stylesheet" href="http://repo.computercraft.org/assets/css/bootstrap.responsive.css" type="text/css" media="screen" charset="utf-8">
        <!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="http://repo.computercraft.org/assets/js/bootstrap.min.js"></script>
    </head>
    
    
    <body class="preview" data-spy="scroll" data-target=".subnav" data-offset="50">
        <section id="navbar">
            <div class="navbar">
                <div class="navbar-inner">
                    
                    
                    <div class="container" style="width:1170px;margin: 0px auto;">
                        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </a>
                        <a class="brand" href="#">ComputerCraft Program Repo</a>
                        <?php if(isset($_SESSION['sqlbans_user']) && isset($_SESSION['sqlbans_bancode']) && $_SESSION['sqlbans_user_ip'] == $_SERVER['REMOTE_ADDR']){
                        ?>
                            <ul class="nav pull-right">
                                <li><a href="http://repo.computercraft.org/pages/check_login.php?logout=true">Logout</a></li>
                            </ul>
                        <?php }else{ ?>
                            <ul class="nav pull-right">
                                <li><a href="http://repo.computercraft.org/login">Login/Register</a></li>
                            </ul>
                        <?php } ?>
                    </div>
                    
                    
                </div>
                
                
            </div>
            
            
        </section>
        
        
        <div class="container">
            
            
            <?php echo $output; ?>
            
            
            <footer class="footer">
                <p class="pull-right">Copyright &copy; 2012 - ComputerCraft</p>
            </footer>
        </div>
    </body>
</html>