<!DOCTYPE html>
<html>
    <head>
        <title>Citizens Script Repo</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" href="http://scripts.citizensnpcs.com/assets/css/bootstrap.min.css" type="text/css" media="screen">
        <link rel="stylesheet" href="http://scripts.citizensnpcs.com/assets/css/bootstrap-responsive.min.css" type="text/css" media="screen">
        <!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <script type="text/javascript" src="http://scripts.citizensnpcs.com/assets/js/bootstrap.min.js"></script>
    </head>
    <body class="preview" data-spy="scroll" data-target=".subnav" data-offset="50">
        <section id="navbar">
            <div class="navbar">
              <div class="navbar-inner">
                <div class="container">
                  <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                  </a>
                  <a class="brand" href="http://scripts.citizensnpcs.com/">Citizens Script Repo</a>
                  <div class="nav-collapse">
                    <ul class="nav">
                      <li{if $activePage=="home"} class="active"{/if}><a href="http://scripts.citizensnpcs.com/">Home</a></li>
                      <li{if $activePage=="list"} class="active"{/if}><a href="http://scripts.citizensnpcs.com/list">List</a></li>
                      <li{if $activePage=="post"} class="active"{/if}><a href="http://scripts.citizensnpcs.com/post">Post</a></li>
                      <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                          <li><a href="#">Action</a></li>
                          <li><a href="#">Another action</a></li>
                          <li><a href="#">Something else here</a></li>
                          <li class="divider"></li>
                          <li class="nav-header">Nav header</li>
                          <li><a href="#">Separated link</a></li>
                          <li><a href="#">One more separated link</a></li>
                        </ul>
                      </li>{if $admin}
                      <li {if $activePage=="admin"} class="active"{/if}><a href="http://scripts.citizensnpcs.com/admin">Admin{if $adminNeeded}(!){/if}</a></li>{/if}
                    </ul>
                    <form class="navbar-search pull-left" id="searchQuery" method="post" action="http://scripts.citizensnpcs.com/search">
                      <input type="text" class="search-query" name='q' placeholder="Search">
                    </form>
                    <ul class="nav pull-right">
                      {if $loggedIn}
                      <li class="dropdown">
                        <a href="" class="dropdown-toggle" data-toggle="dropdown">{$username} <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                          <li><a href="http://scripts.citizensnpcs.com/user/{$username}">Your Profile</a></li>
                          <li><a href="http://scripts.citizensnpcs.com/settings">Settings</a></li>
                          <li><a href="#">Something else here</a></li>
                          <li class="divider"></li>
                          <li><a href="http://scripts.citizensnpcs.com/logout">Logout</a></li>
                        </ul>
                      </li>
                      {else}
                      <li>
                        <ul class="nav">
                          <li {if $activePage=="login"}class="active"{/if}><a href="http://scripts.citizensnpcs.com/login">Login/Register</a></li>
                        </ul>
                      </li>
                      {/if}
                    </ul>
                  </div><!-- /.nav-collapse -->
                </div>
              </div><!-- /navbar-inner -->
            </div><!-- /navbar -->
        </section>
        <div class="container">
            <div class="alert alert-info">
                <strong>Notice!</strong> This site is still in development. Some features may not work properly.
            </div>
            {include file="$output"}
            <footer class="footer">
                <div style="padding-bottom:12px; text-align:center;">Copyright &copy; 2012 - CitizensNPCs<br><a href="http://scripts.citizensnpcs.com/support">Support</a></div>
            </footer>
        </div>
    </body>
</html>