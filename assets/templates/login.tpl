<form id='login' method='post' accept-charset='UTF-8'>
    <fieldset>
        <legend>Login</legend>
        <br />
        <div class="well well-large">
        {if $loginError }<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">X</button><strong>{$loginError}</strong></div><br /><br />{/if}
        {if $loginMessage}<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">X</button><strong>{$loginMessage}</strong></div>{/if}
        {if $loginInfo}<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">X</button><strong>{$loginInfo}</strong></div>{/if}
        
        <input type='hidden' name='loginForm' id='loginForm' value='1'/>
        {if $userError}<div class="control-group error">{/if}<label for='username' >Username:</label>
        <input class="control-label" type='text' {if $username} value="{$username}" {/if}name='username' id='username' maxlength="50" />{if $userError}</div>{/if}
        {if $passwordError}<div class="control-group error">{/if}<label for='password' >Password:</label>
        <input class="control-label" type='password' name='password' id='password' maxlength="50" /><br />{if $passwordError}</div>{/if}
        <input class="btn btn-primary" type='submit' name='Submit' value='Submit' />
        <br /><br /><p>Don't have a login? <a href="register">Register now!</a>
        <br />Forgot your password? <a href="{buildURL page='recover'}">Recover it!</a></p>
        </div>
    </fieldset>
</form>