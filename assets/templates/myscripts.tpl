<div style="background-color:white" class="span8 well well-small">
    <h3 style="text-align:center;">Scripts</h3>
    <table class="table table-hover">
        {foreach $resultArray as $result}{$pubID = $result.pubID}
            {if $result}<tr style="cursor:pointer" onclick='document.location.href="{buildURL page='view/'}{$result.pubID}"'><td>
            <span class="pull-left"><a href="{buildURL page='view/'}{$result.pubID}">{$result.name}</a></span>  <span class="pull-right" data-placement="right" rel="tooltip" title="Views"><i class="icon-eye-open"></i> {$result.views}</span>
            <br><span class="pull-right" data-placement="right" rel="tooltip" title="Likes"><i class="icon-thumbs-up"></i> {if isset($likesArray.$pubID)}{$likesArray.$pubID}{else}0{/if}</span>
            <br><small>Author: {$result.author}</small><span class="pull-right" data-placement="right" rel="tooltip" title="Downloads"><i class="icon-download"></i> {$result.downloads}</span>
            </td></tr>{/if}
        {foreachelse}
            <tr><td><p style="text-align:center;">You haven't posted any scripts! <a href="{buildURL page='post'}">Post one now!</a></span></td></tr>
        {/foreach}
    </table>
</div>