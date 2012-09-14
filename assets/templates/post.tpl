<form id='post' method='post' accept-charset='UTF-8'>
    <fieldset>
        <legend>Post a new script</legend>
        <p>Here you can post a new script. You must fill out a description and the script code.</p>
        <div class="well well-large">
            {if $postError }<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">X</button><strong>{$postError}</strong></div>{/if}
            {if $nameError}<div class="control-group error">{/if}<label for='name' >Name:</label>
            <input class="input-large" name='name' id='name' type="text"{if $name} value="{$name}"{/if} /><br>{if $nameError}</div>{/if}
            {if $descriptionError}<div class="control-group error">{/if}<label for='Description' >Description:</label>
            <textarea id='Description' name='Description' rows="6" class="span7">{$description}</textarea>{if $descriptionError}</div>{/if}
            {if $scriptError}<div class="control-group error">{/if}<label for='scriptCode' >Script Code:</label>
            <textarea id='scriptCode' name='scriptCode' rows="10" class="span7">{$scriptCode}</textarea>{if $scriptError}</div>{/if}
            {if $tagError}<div class="control-group error">{/if}<label for='tags' >Tags:</label>
            <input class="input-large" name='tags' id='tags' type="text"{if $tags} value="{$tags}"{/if} /><br>{if $tagError}</div>{/if}
            <small class="muted">Separate tags with a comma.</small><br><br>
            {if $typeError}<div class="control-group error">{/if}<label>Type of code:</label>
            <input type="hidden" name="typeOfScript" value="None" />
            <script>
                $('.nav-tabs').button()
                
            </script>
            <div class="btn-group" data-toggle="buttons-radio">
                <input onclick="this.form.elements['typeOfScript'].value = '1';" id="type1" type="button" class="btn" value="Citizens Script" />
                <input onclick="this.form.elements['typeOfScript'].value = '2';" id="type2" type="button" class="btn" value="Denizen Script" />
                <input onclick="this.form.elements['typeOfScript'].value = '3';" id="type3" type="button" class="btn" value="Uhh... Script" />
            </div>{if $typeError}</div>{/if}
            <small class="muted">Pick one!</small><br><br>
            <label for='privacy'>Privacy:</label>
            <input type="checkbox" name="privacy" id="privacy" value="2" /> Make my script unlisted (won't appear in search results).
            <br><br><br>
            <button class="btn" type="button">Cancel</button> <input class="btn btn-primary" type='Submit' name='SubmitScript' value=' Save ' />
        </div>
    </fieldset>
</form>