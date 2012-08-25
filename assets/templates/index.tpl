{include file="header.tpl"}
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
                        <a class="brand" href="http://repo.computercraft.org/">ComputerCraft Program Repo</a>
                            <ul class="nav pull-right">
                                <li>
                                    {if $loginStatus == 1}<a href="http://repo.computercraft.org/logout">Logout</a>{else}<a href="http://repo.computercraft.org/login">Login/Register</a>{/if}
                                </li>
                            </ul>
                    </div>
                </div>
            </div>
        </section>
        <div class="container">
            {include file="$output"}



{include file="footer.tpl"}