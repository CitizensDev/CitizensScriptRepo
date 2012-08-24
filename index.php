<?php
session_start();

// Get the arguments from the url
$_SERVER['REQUEST_URI_PATH'] = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
$args = explode('/', trim($_SERVER['REQUEST_URI_PATH'], '/'));

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
        $pubID = strtolower($args[1]);
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
            
            
            <?php include($output); ?>
            
            
            <footer class="footer">
                <p class="pull-right">Copyright &copy; 2012 - ComputerCraft</p>
            </footer>
        </div>
    </body>
</html>