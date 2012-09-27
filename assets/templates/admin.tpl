<div class="well well-large">
    <table class="table table-hover">
        <thead>
            <tr><th>Reportee</th><th>Flag type</th></tr>
        </thead>
        <tbody>{foreach $flagArray as $flagItem}
            <tr style="cursor:pointer;" onclick="document.location.href='{buildURL page='view/'}{$flagItem.flaggedID}'"><td>{$flagItem.author}</td><td>{if $flagItem.type==1}Script{elseif $flagItem.type==2}Comment{/if}</td></tr>
            {foreachelse}
                <tr><td>No items have been flagged!</td></tr>
            {/foreach}
        </tbody>
    </table>
</div>