<form id='post' method='post' accept-charset='UTF-8'>
    <fieldset>
        <legend>Post a new script</legend>
        <p>Here you can post a new script. You must fill out a description and the script code.</p>
        <div class="well well-large">
            {if $postError }<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">X</button><strong>{$postError}</strong></div>{/if}
            {if $nameError}<div class="control-group error">{/if}<label for='name' >Name:</label>
            <input class="input-large" name='name' id='name' type="text"{if $name} value="{$name}"{/if} /><br>{if $nameError}</div>{/if}
            {if $descriptionError}<div class="control-group error">{/if}<label for='Description' >Description:</label>
            <textarea id='Description' name='Description' rows="15" class="span7">{$description}</textarea>{if $descriptionError}</div>{/if}
            {if $scriptError}<div class="control-group error">{/if}<label for='scriptCode' >Script Code:</label>
            <textarea id='scriptCode' name='scriptCode' rows="10" class="span7">{$scriptCode}</textarea>{if $scriptError}</div>{/if}
            {if $tagError}<div class="control-group error">{/if}<label for='tags' >Tags:</label>
            <input class="input-large" name='tags' id='tags' type="text"{if $tags} value="{$tags}"{/if} /><br>{if $tagError}</div>{/if}
            {if $dVersionError}<div class="control-group error">{/if}<label for='dVersion' >Denizen Version:</label>
            <input class="input-small" name='dVersion' id='dVersion' type="text"{if $denizen_version} value="{$denizen_version}"{/if} /><br>{if $dVersionError}</div>{/if}
            <small class="muted">Separate tags with a comma.</small><br><br>
            <small class="muted">Pick one!</small><br><br>
            <label for='privacy'>Privacy:</label>
            <input type="checkbox" name="privacy" id="privacy" value="2" /> Make my script unlisted (won't appear in search results).<br>
            <label for='dscript'>DScript:</label>
            <input type="checkbox" name="dscript" id="dscript" value="1" /> Script is safe for use without any editing (not dependent on locations or NPC names).
            <br><br><br>
            <button class="btn" type="button">Cancel</button> <input class="btn btn-primary" type='Submit' name='SubmitScript' value=' Save ' />
        </div>
    </fieldset>
</form>