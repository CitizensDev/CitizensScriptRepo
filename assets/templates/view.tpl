<div class="span7 well well-small">
    <legend>{$dataToUse.name}</legend>
    {$code}
</div>
<div class="span3 offset1 well well-large">
    <b>Author: </b>{$dataTouse.author}<br>
    <b>Created: </b>9/13/2012<br>
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