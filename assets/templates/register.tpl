<form id='register' action='' method='post' accept-charset='UTF-8'>
    <fieldset>
        <legend>Register</legend>
        <p>An account is required for most of the actions on the site. Your email address is required both to allow for password recovery and prevent spam.</p>
        <br />
        <div class="well well-large">
        {if $registerError }<div class="alert alert-error"><strong>{$registerError}</strong></div><br /><br />{/if}
        <input type='hidden' name='register' id='register' value='1'/>
        {if $usernameError}<div class="control-group error">{/if}<label for='username' >Username:</label>
        <input class="control-label" type='text' {if $username} value="{$username}" {/if}name='username' id='username' maxlength="50" />{if $usernameError}</div>{/if}
        {if $emailError}<div class="control-group error">{/if}<label for='email' >Email Address:</label>
        <input class="control-label" type='text' {if $email} value="{$email}" {/if}name='email' id='email' maxlength="50" />{if $emailError}</div>{/if}
        {if $passwordError}<div class="control-group error">{/if}<label for='password' >Password:</label>
        <input class="control-label" type='password' name='password' id='password' maxlength="50" /><br />
        <label for='password' >Repeat Password:</label>
        <input class="control-label" type='password' name='passwordConfirm' id='password' maxlength="50" /><br />{if $passwordError}</div>{/if}
        {if $captchaError}<div class="control-group error">{/if}<label for='recaptcha' >reCAPTCHA:</label>{if $captchaError}</div>{/if}
        {$recaptcha}
        <br /><br /><p><small>All fields are required</small></p>
        <input class="btn btn-primary" type='submit' name='Submit' value='Submit' />
        </div>
    </fieldset>
</form>