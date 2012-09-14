<div class="span8 well well-small">
    <b>Description: </b>{$dataToUse.description}<br><br>
    {$code}
    <b style="text-align:right;padding-left:0px;">Edit</b>
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
                    <td>Some data, mainy {$comment}.</td>
                </tr>
            {foreachelse}
                <tr>
                    <td><p>No one has posted a comment! Post one below:</p></td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>
<form id='post' method='post' accept-charset='UTF-8'>
    <fieldset>
        <textarea rows="4" class="span7" id="commentField" name="commentField" placeholder="Comment"></textarea><br>
        <input class="btn btn-small btn-primary" type='Submit' name='Submit' value='Submit' /><br>
    </fieldset>
</form>