<div class="span8 well well-small">
    <b>Description: </b>{$dataToUse.description}<br><br>
    {$code}
    {if $loggedIn}{if $dataToUse.author==$username}<b style="text-align:right;padding-left:0px;">Edit</b>{/if}{/if}
</div>
<div class="span3 well well-large">
    <h4 style="text-align:center;">{$dataToUse.name}</h2><br>
    <b>Author: </b>{$dataToUse.author}<br>
    <b>Created: </b>{$dataToUse.timestamp}<br>
    <b>Views: </b>{$dataToUse.views}<br>
    <b>Downloads: </b>{$dataToUse.downloads}<br>
    <b>Likes: </b>{$dataToUse.likes}<br>
</div><br><br><br><br>
<div id="commentsZone">
    <legend>Comments</legend>
    <table class="table table-bordered">
        <tbody>
            {foreach $commentData as $comment}
                <tr>
                    <td><small>{$comment.timestamp}</small> - <a href="http://scripts.citizensnpcs.com/user/{$comment.author}">{$comment.author}</a>: 
                    <br>
                    <br>{$comment.content}.</td>
                </tr>
            {foreachelse}
                <tr>
                    <td>No one has posted a comment! Post one below:</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>
{if $commentFailure}<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">X</button>{$commentFailure}</div>{/if}
{if $commentSuccess}<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">X</button>{$commentSuccess}</div>{/if}
<form id='post' method='post' accept-charset='UTF-8'>
    <fieldset>
        <textarea rows="4"{if !$loggedIn}disabled{/if} class="span7" id="commentField" name="commentField" placeholder="{if $loggedIn}Comment{else}Log in to comment!{/if}">{if $commentField}{$commentField}{/if}</textarea><br>
        <input class="btn btn-small btn-primary" type='Submit' name='Submit' value='Submit' /><br>
    </fieldset>
</form>