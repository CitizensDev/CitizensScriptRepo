<div style="background-color:white" class="offset2 span8 well well-small">
    <h3 style="text-align:center;">Scripts</h3>
    {*{include file="pagination.tpl"}*}
    <table class="table table-hover">
        {foreach $resultArray as $result}{$pubID = $result.pubID}
            {if $result}<tr style="cursor:pointer" onclick='document.location.href="{buildURL page='view/'}{$result.pubID}"'><td>
            <span class="pull-left"><a href="{buildURL page='view/'}{$result.pubID}">{$result.name}</a></span>  <span class="pull-right" data-placement="right" rel="tooltip" title="Views"><i class="icon-eye-open"></i> {$result.views}</span>
            <br><small><b>Author:</b> {$result.author} <b style="padding-left:5em;">Created:</b> {$result.timestamp|date_format:"%D"}{if $result.timestamp!=$result.edited} <b style="padding-left:5em;">Edited:</b> {$result.edited|date_format:"%D"} {/if}</small><span class="pull-right" data-placement="right" rel="tooltip" title="Likes"><i class="icon-thumbs-up"></i> {if $result.author=="AgentKid"} &infin;{else}{$result.likes}{/if}</span>
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
            {if $listingType!="all"}<li><a href="{buildURL page='browse/'}all/{$sortType}/1/{$resultsPerPage}/">All Script Types</a></li>{/if}
            {if $listingType!="citizens"}<li><a href="{buildURL page='browse/'}citizens/{$sortType}/1/{$resultsPerPage}/">Only Citizens Scripts</a></li>{/if}
            {if $listingType!="denizens"}<li><a href="{buildURL page='browse/'}denizens/{$sortType}/1/{$resultsPerPage}/">Only Denizen Scripts</a></li>{/if}
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
    </div><br>
    <div class="btn-group">
        <a class="btn dropdown-toggle" data-toggle="dropdown">{if $resultsPerPage==20}20 results per page.{elseif $resultsPerPage==50}50 results per page.{elseif $resultsPerPage==100}100 results per page.{elseif $resultsPerPage==200}200 results per page.{/if} <span class="caret"></span></a>
        <ul class="dropdown-menu">
            {if $resultsPerPage!=20}<li><a href="{buildURL page='browse/'}{$listingType}/{$sortType}/{$resultPageNumber}/20/">Show 20 results per page</a></li>{/if}
            {if $resultsPerPage!=50}<li><a href="{buildURL page='browse/'}{$listingType}/{$sortType}/{$resultPageNumber}/50/">Show 50 results per page</a></li>{/if}
            {if $resultsPerPage!=100}<li><a href="{buildURL page='browse/'}{$listingType}/{$sortType}/{$resultPageNumber}/100/">Show 100 results per page</a></li>{/if}
            {if $resultsPerPage!=200}<li><a href="{buildURL page='browse/'}{$listingType}/{$sortType}/{$resultPageNumber}/200/">Show 200 results per page</a></li>{/if}
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
<div class="span11" id="navigation" style="text-align:center;padding-bottom:12px">
    <div class="pagination pagination-centered">
        <ul>
            {if $resultPageNumber==1}<li class="disabled"><a>Prev</a></li>
            {else}<li><a href="{buildURL page='browse/'}{$listingType}/{$sortType}/{math equation="x-1" x=$resultPageNumber}/{$resultsPerPage}/">Prev</a></li>
            {/if}{foreach $resultPages as $pageItem}{$maxPage = $pageItem}
            <li{if $pageItem==$resultPageNumber} class="disabled"{/if}><a{if $pageItem!=$resultPageNumber} href="{buildURL page='browse/'}{$listingType}/{$sortType}/{$pageItem}/{$resultsPerPage}/"{/if}>{$pageItem}</a></li>{/foreach}
            {if $resultPageNumber==$maxPage}<li class="disabled"><a>Next</a></li>
            {else}<li><a href="{buildURL page='browse/'}{$listingType}/{$sortType}/{math equation="x+1" x=$resultPageNumber}/{$resultsPerPage}/">Next</a></li>
        {/if}</ul>
    </div>
</div><br><br><br>