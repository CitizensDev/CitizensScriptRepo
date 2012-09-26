<div style="background-color:white" class="span8 well well-small">
    <h3 style="text-align:center;">Scripts</h3>
    <table class="table table-hover">
        {foreach $resultArray as $result}{$pubID = $result.pubID}
            {if $result}<tr style="cursor:pointer" onclick='document.location.href="{buildURL page='view/'}{$result.pubID}"'><td>
            <span class="pull-left"><a href="{buildURL page='view/'}{$result.pubID}">{$result.name}</a></span>  <span class="pull-right" data-placement="right" rel="tooltip" title="Views"><i class="icon-eye-open"></i> {$result.views}</span>
            <br><small>Author: {$result.author}</small><span class="pull-right" data-placement="right" rel="tooltip" title="Likes"><i class="icon-thumbs-up"></i> {if $result.author=="AgentKid"} &infin;{else}{$result.likes}{/if}</span>
            <br><small><span class="muted">Tags: {$result.tags}</span></small><span class="pull-right" data-placement="right" rel="tooltip" title="Downloads"><i class="icon-download"></i> {$result.downloads}</span>
            </td></tr>{/if}
        {/foreach}
    </table>
</div>
<div style="background-color:white" class="span3 well well-small">
    <h5>Options:</h5>
    <div class="btn-group">
        <a class="btn dropdown-toggle" data-toggle="dropdown">{if $listingType=="all"}Displaying all script types{elseif $listingType=="citizens"}Displaying only Citizens Scripts{elseif $listingType=="denizens"}Displaying only Denizen Scripts{/if} <span class="caret"></span></a>
        <ul class="dropdown-menu">
            {if $listingType!="all"}<li><a href="{buildURL page='browse/'}all/{$sortType}/{$resultPageNumber}/{$resultsPerPage}/">All Script Types</a></li>{/if}
            {if $listingType!="citizens"}<li><a href="{buildURL page='browse/'}citizens/{$sortType}/{$resultPageNumber}/{$resultsPerPage}/">Only Citizens Scripts</a></li>{/if}
            {if $listingType!="denizens"}<li><a href="{buildURL page='browse/'}denizens/{$sortType}/{$resultPageNumber}/{$resultsPerPage}/">Only Denizen Scripts</a></li>{/if}
        </ul>
    </div><br>
    <div class="btn-group">
        <a class="btn dropdown-toggle" data-toggle="dropdown">{if $sortType=="newest"}Sorting newest to oldest.{elseif $sortType=="oldest"}Sorting oldest to newest.{elseif $sortType=="mostLiked"}Sorting by most liked.{elseif $sortType=="mostViewed"}Sorting by most viewed.{elseif $sortType=="mostDownloads"}Sorting by most downloaded.{/if} <span class="caret"></span></a>
        <ul class="dropdown-menu">
            {if $sortType!="newest"}<li><a href="{buildURL page='browse/'}{$listingType}/newest/{$resultPageNumber}/{$resultsPerPage}/">Sort by New to Old</a></li>{/if}
            {if $sortType!="oldest"}<li><a href="{buildURL page='browse/'}{$listingType}/oldest/{$resultPageNumber}/{$resultsPerPage}/">Sort by Old to New</a></li>{/if}
            {if $sortType!="mostLiked"}<li><a href="{buildURL page='browse/'}{$listingType}/mostLiked/{$resultPageNumber}/{$resultsPerPage}/">Sort by number of likes</a></li>{/if}
            {if $sortType!="mostViewed"}<li><a href="{buildURL page='browse/'}{$listingType}/mostViewed/{$resultPageNumber}/{$resultsPerPage}/">Sort by number of views</a></li>{/if}
            {if $sortType!="mostDownloads"}<li><a href="{buildURL page='browse/'}{$listingType}/mostDownloads/{$resultPageNumber}/{$resultsPerPage}/">Sort by number of downloads</a></li>{/if}
        </ul>
    </div>
</div>
<div style="background-color:white" class="span3 well well-small">
    <h4 style="text-align:center;">Users</h4>
    <table class="table table-hover">
        {foreach $userArray as $user}{if $user}<tr style="cursor:pointer" onclick='document.location.href="{buildURL page='user/'}{$user.username}"'><td>
            <a href="{buildURL page='user/'}{$user.username}">{if $user.staff==1}<i class="icon-star"></i> {/if}{$user.username}</a></td></tr>
        {/if}{/foreach}
    </table>
</div>
<!-- Navigation -->
<div id="navigation" style="text-align:center;">
    Results per page: {if $resultsPerPage!=20}<a href="{buildURL page='browse/'}{$listingType}/{$sortType}/{$resultPageNumber}/20">{/if}20{if $resultsPerPage!=20}</a>{/if}, {if $resultsPerPage!=50}<a  href="{buildURL page='browse/'}{$listingType}/{$resultPageNumber}/50">{/if}50{if $resultsPerPage!=50}</a>{/if}, {if $resultsPerPage!=100}<a  href="{buildURL page='browse/'}{$listingType}/{$resultPageNumber}/100">{/if}100{if $resultsPerPage!=100}</a>{/if}, {if $resultsPerPage!=200}<a  href="{buildURL page='browse/'}{$listingType}/{$resultPageNumber}/200">{/if}200{if $resultsPerPage!=200}</a>{/if}
    <div class="pagination pagination-centered">
        <ul>
            {if $resultPageNumber==1}<li class="disabled"><a>Prev</a></li>
            {else}<li><a href="{buildURL page='browse/'}{$listingType}/{math equation="x-1" x=$resultPageNumber}/{$resultsPerPage}/">Prev</a></li>
            {/if}{foreach $resultPages as $pageItem}{$maxPage = $pageItem}
            <li{if $pageItem==$resultPageNumber} class="disabled"{/if}><a{if $pageItem!=$resultPageNumber} href="{buildURL page='browse/'}{$listingType}/{$sortType}/{$pageItem}/{$resultsPerPage}/"{/if}>{$pageItem}</a></li>{/foreach}
            {if $resultPageNumber==$maxPage}<li class="disabled"><a>Next</a></li>
            {else}<li><a href="{buildURL page='browse/'}{$listingType}/{$sortType}/{math equation="x+1" x=$resultPageNumber}/{$resultsPerPage}/">Next</a></li>
        {/if}</ul>
    </div>
</div><br><br><br>