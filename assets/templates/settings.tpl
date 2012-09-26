<form id='register' method='post' accept-charset='UTF-8'>
    <fieldset>
        <legend>Account Settings</legend>
        <p>On this page you can change your account settings. To change your email, you must <a href="{buildURL page='support'}">contact us.</a><br>
        <div class="well well-large">
            {if $successMessage }<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">X</button><strong>{$successMessage}</strong></div>{/if}
            <h4>There aren't any settings yet!</h4>
            <br><button class="btn" type="button">Cancel</button> <input class="btn btn-primary" type="submit" name="Save" />
        </div>
    </fieldset>
</form>