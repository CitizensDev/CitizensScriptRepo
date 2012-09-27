<div class="well well-small span3">
    <table class="table">
        <thead><tr><th><h4 style="text-align:center;">{$usernameForPage}</h4></th></tr></thead>
        <tr><td><b>Joined:</b> <abbr class="timeago" title="{$user.registered}">{$user.registered}</abbr></td></tr>
        <tr><td><b>Scripts Posted:</b> {$scriptsPosted}</td></tr>
        <tr><td><b>Comments Added:</b> {$commentsAdded}</td></tr>
        <tr><td><b>Scripts Liked:</b> {$scriptsLiked}</td></tr>{if $user.staff==1}
        <tr><td><b><i class="icon-star"></i> This user is Staff</b></td></tr>{/if}
    </table>
</div>
<div class="well span8">
    <h3 style="text-align:center;">Scripts</h3>
    <table class="table table-hover">
        {foreach $resultArray as $result}{$pubID = $result.pubID}
            {if $result}<tr style="cursor:pointer" onclick='document.location.href="{buildURL page='view/'}{$result.pubID}"'><td>
            <span class="pull-left"><a href="{buildURL page='view/'}{$result.pubID}">{$result.name}</a></span>  <span class="pull-right" data-placement="right" rel="tooltip" title="Views"><i class="icon-eye-open"></i> {$result.views}</span>
            <br><span class="pull-right" data-placement="right" rel="tooltip" title="Likes"><i class="icon-thumbs-up"></i> {if isset($likesArray.$pubID)}{$likesArray.$pubID}{else}0{/if}</span>
            <br><small>Author: {$result.author}</small><span class="pull-right" data-placement="right" rel="tooltip" title="Downloads"><i class="icon-download"></i> {$result.downloads}</span>
            </td></tr>{/if}
        {foreachelse}
            <tr><td><p style="text-align:center;">This user hasn't posted any scripts!</p></td></tr>
        {/foreach}
    </table>
</div>
<div width="100%"><br></div>