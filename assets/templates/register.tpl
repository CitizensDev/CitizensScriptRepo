<form id='register' method='post' accept-charset='UTF-8'>
    <fieldset>
        <legend>Register</legend>
        <p>An account is required for most of the actions on the site. Your email address is required both to allow for password recovery and prevent spam registrations.</p>
        <br />
        <div class="well well-large">
        {if $registerError }<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">X</button><strong>{$registerError}</strong></div>{/if}
        <input type='hidden' name='registerForm' id='registerForm' value='1'/>
        {if $usernameError}<div class="control-group error">{/if}<label for='username' >Username:</label>
        <input class="control-label" type='text' {if $username} value="{$username}" {/if}name='username' id='username' maxlength="50" />{if $usernameError}</div>{/if}
        {if $emailError}<div class="control-group error">{/if}<label for='email' >Email Address:</label>
        <input class="control-label" type='email' {if $email} value="{$email}" {/if}name='email' id='email' maxlength="50" />{if $emailError}</div>{/if}
        {if $passwordError}<div class="control-group error">{/if}<label for='password' >Password:</label>
        <input class="control-label" type='password' name='password' id='password' maxlength="50" /><br />
        <label for='passwordConfirm' >Repeat Password:</label>
        <input class="control-label" type='password' name='passwordConfirm' id='passwordConfirm' maxlength="50" /><br />{if $passwordError}</div>{/if}
        {if $ayahError}<div class="control-group error">{/if}<label>Are You A Human?:</label>{if $ayahError}</div>{/if}
        {$ayah}
        <br /><br /><p><small>All fields are required</small></p>
        <input class="btn btn-primary" type='Submit' name='Submit' value=' GO ' />
        </div>
    </fieldset>
</form>