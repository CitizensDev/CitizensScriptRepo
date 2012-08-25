<form id='login' action='' method='post' accept-charset='UTF-8'>
    <fieldset>
        <legend>Login</legend>
        <br />
        {if $loginError }<strong>{$loginError}</strong><br /><br />{/if}
        
        <input type='hidden' name='login' id='login' value='1'/>
        <label for='username' >Username:</label>
        <input class="control-label" type='text' {if $username} value="{$username}" {/if}name='username' id='username' maxlength="50" />
        {if $passwordError}<div class="control-group error">{/if}<label for='password' >Password:</label>
        <input class="control-label" type='password' name='password' id='password' maxlength="50" /><br />
        <input class="btn btn-primary" type='submit' name='Submit' value='Submit' />
        <br /><br /><p>Don't have a login? <a href="register">Register now!</a></p>
    </fieldset>
</form>