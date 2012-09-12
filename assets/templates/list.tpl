THIS IS A LIST! RAWR!<br>

The links should look like<br> http://scripts.citizensnpcs.com/list/{$resultPageNumber}/{$resultsPerPage}

<!-- This is broken. <div class="container-fluid">
	<div clss="row-fluid">
		<div class="span10">
			THIS IS THE BODY :D
		</div>
		<div class="span2">
			Sidebar stuff
		</div>
	</div>
</div> -->

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