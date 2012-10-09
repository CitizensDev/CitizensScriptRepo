<!DOCTYPE html>{function name=buildURL}{$ScriptRepo->mainSite}{$page}{/function}
<html>
    <head>
        <title>Citizens Script Repo</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" href="{buildURL page='assets/css/bootstrap.min.css'}" type="text/css" media="screen">
        <!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <script type="text/javascript" src="{buildURL page='assets/js/bootstrap.min.js'}"></script>
        <script type="text/javascript" src="{buildURL page='assets/js/jquery.timeago.js'}"></script>
        {if $activePage=="post" || $activePage=="edit"}<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/reset-fonts/reset-fonts.css"> 
<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/base/base-min.css"> 
<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/assets/skins/sam/skin.css"> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js"></script> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/element/element-min.js"></script> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/datasource/datasource-min.js"></script> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/dragdrop/dragdrop-min.js"></script> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/datatable/datatable-min.js"></script> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/container/container_core-min.js"></script> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/menu/menu-min.js"></script> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/button/button-min.js"></script> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/editor/editor-min.js"></script> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/resize/resize-min.js"></script>{/if}
        <script>
            jQuery(document).ready(function() {
                jQuery("abbr.timeago").timeago();
            });
            $(document).ready(function () {
                if ($("[rel=tooltip]").length) {
                    $("[rel=tooltip]").tooltip();
                }
            });
            $("#myModal").modal() 
        </script>
    </head>
    <body class="{if $activePage=="post" || $activePage=="edit"}yui-skin-sam {/if}preview" data-spy="scroll" data-target=".subnav" data-offset="50">
        <section id="navbar">
            <div class="navbar">
              <div class="navbar-inner">
                <div class="container">
                  <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                  </a>
                  <a class="brand" href="{buildURL page=''}">Citizens Script Repo</a>
                  <div class="nav-collapse">
                    <ul class="nav">
                      <li{if $activePage=="home"} class="active"{/if}><a href="{buildURL page=''}"><i class="icon-home"></i> Home</a></li>
                      <li{if $activePage=="browse"} class="active"{/if}><a href="{buildURL page='browse'}"><i class="icon-list"></i> Browse</a></li>
                      <li{if $activePage=="post"} class="active"{/if}><a href="{buildURL page='post'}"><i class="icon-pencil"></i> Post</a></li>
                      <!-- Hmmmm, what to put here....<li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Click Me! <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                          <li><a href="#">Someone should</a></li>
                          <li><a href="#">tell aufdemrand</a></li>
                          <li><a href="#">to come up with</a></li>
                          <li class="divider"></li>
                          <li class="nav-header">something to</li>
                          <li><a href="#">go here, or else</a></li>
                          <li><a href="#">this will stay</a></li>
                        </ul> 
                      </li>-->{if $ScriptRepo->admin} 
                      <li {if $activePage=="admin"} class="active"{/if}><a href="{buildURL page='admin'}">Admin {if $adminNeeded}<i class="icon-warning-sign"></i>{/if}</a></li>{/if}
                    </ul>
                    <form class="navbar-search pull-left" id="searchQuery" method="post" action="{buildURL page='search'}">
                      <input type="text" class="search-query" name='q' placeholder="Search">
                    </form>
                    <ul class="nav pull-right">
                      {if $ScriptRepo->loggedIn}
                      <li class="dropdown">
                        <a href="" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user"></i> {$ScriptRepo->username} <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                          <li><a href="{buildURL page='user/'}{$ScriptRepo->username}">Your Profile</a></li>
                          <li><a href="{buildURL page='settings'}">Settings</a></li>
                          <li><a href="{buildURL page='myscripts'}">Your scripts</a></li>
                          <li class="divider"></li>
                          <li><a href="{buildURL page='logout'}">Logout</a></li>
                        </ul>
                      </li>
                      {else}
                      <li>
                        <ul class="nav">
                          <li {if $activePage=="login"}class="active"{/if}><a href="{buildURL page='login'}">Login/Register</a></li>
                        </ul>
                      </li>
                      {/if}
                    </ul>
                  </div><!-- /.nav-collapse -->
                </div>
              </div><!-- /navbar-inner -->
            </div><!-- /navbar -->
        </section>
        <div{if $activePage!="view"} class="container container-fluid"{else} width="100%" style="margin-left:2em;margin-right:2em;"{/if}>
            <!--<div class="alert alert-info">
                <strong>Notice!</strong> This site is the Citizens Script Repo v2.3 - Backend restructuring finished :D
            </div> -->
            {include file="$output"}
            <footer class="footer{if $activePage!="view"} span11"{else}" width="100%"{/if}>
                <div style="padding-bottom:12px; text-align:center;">Copyright &copy; 2012 - CitizensNPCs<br><a href="{buildURL page='support'}">Support</a> - <a href="{buildURL page='credits'}">Credits</a></div>
            </footer>
        </div>
    </body>
</html>