<form id='login' action='' method='post' accept-charset='UTF-8'>
    <fieldset>
        <legend>Login</legend>
        <br />
        <div class="well well-large">
        {if $loginError }<div class="alert alert-error"><strong>{$loginError}</strong></div><br /><br />{/if}
        {if $registerFinished}<div class="alert alert-success"><strong>{$registerFinished}</strong></div><br /><div class="alert alert-info"><strong>Email confirmation is currently broken.</strong></div><br />{/if}
        
        <input type='hidden' name='login' id='login' value='1'/>
        <label for='username' >Username:</label>
        <input class="control-label" type='text' {if $username} value="{$username}" {/if}name='username' id='username' maxlength="50" />
        {if $passwordError}<div class="control-group error">{/if}<label for='password' >Password:</label>
        <input class="control-label" type='password' name='password' id='password' maxlength="50" /><br />{if $passwordError}</div>{/if}
        <input class="btn btn-primary" type='submit' name='Submit' value='Submit' />
        <br /><br /><p>Don't have a login? <a href="register">Register now!</a>
        <br />Forgot your password? <a href="http://repo.computercraft.org/recover">Recover it!</a></p>
        </div>
    </fieldset>
</form>