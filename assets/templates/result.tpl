<div style="background-color:white" class="span8 well well-small">
    <h3 style="text-align:center;">Scripts</h3>
    <table class="table table-hover">
        {foreach $resultArray as $result}{$pubID = $result.pubID}
            {if $result}<tr style="cursor:pointer" onclick='document.location.href="http://scripts.citizensnpcs.com/view/{$result.pubID}"'><td>
            <span class="pull-left"><a href="http://scripts.citizensnpcs.com/view/{$result.pubID}">{$result.name}</a></span>  <span class="pull-right" data-placement="right" rel="tooltip" title="Views"><i class="icon-eye-open"></i> {$result.views}</span>
            <br><span class="pull-right" data-placement="right" rel="tooltip" title="Likes"><i class="icon-thumbs-up"></i> {if $result.author=="AgentKid"} &infin;{else}{if isset($likesArray.$pubID)}{$likesArray.$pubID}{else}0{/if}{/if}</span>
            <br><small>Author: {$result.author}</small><span class="pull-right" data-placement="right" rel="tooltip" title="Downloads"><i class="icon-download"></i> {$result.downloads}</span>
            </td></tr>{/if}
        {/foreach}
    </table>
</div>
<!-- Navigation -->
<div id="navigation" style="text-align:center;">
    Results per page: {if $resultsPerPage!=20}<a href="http://scripts.citizensnpcs.com/list/{$resultPageNumber}/20">{/if}20{if $resultsPerPage!=20}</a>{/if}, {if $resultsPerPage!=50}<a  href="http://scripts.citizensnpcs.com/list/{$resultPageNumber}/50">{/if}50{if $resultsPerPage!=50}</a>{/if}, {if $resultsPerPage!=100}<a  href="http://scripts.citizensnpcs.com/list/{$resultPageNumber}/100">{/if}100{if $resultsPerPage!=100}</a>{/if}, {if $resultsPerPage!=200}<a  href="http://scripts.citizensnpcs.com/list/{$resultPageNumber}/200">{/if}200{if $resultsPerPage!=200}</a>{/if}
    <div class="pagination pagination-centered">
        <ul>
            {if $resultPageNumber==1}<li class="disabled"><a>Prev</a></li>
            {else}<li><a href="http://scripts.citizensnpcs.com/list/{math equation="x-1" x=$resultPageNumber}/{$resultsPerPage}/">Prev</a></li>
            {/if}{foreach $resultPages as $pageItem}{$maxPage = $pageItem}
            <li{if $pageItem==$resultPageNumber} class="disabled"{/if}><a{if $pageItem!=$resultPageNumber} href="http://scripts.citizensnpcs.com/list/{$pageItem}/{$resultsPerPage}/"{/if}>{$pageItem}</a></li>{/foreach}
            {if $resultPageNumber==$maxPage}<li class="disabled"><a>Next</a></li>
            {else}<li><a href="http://scripts.citizensnpcs.com/list/{math equation="x+1" x=$resultPageNumber}/{$resultsPerPage}/">Next</a></li>
        {/if}</ul>
    </div>
</div><br><br><br>