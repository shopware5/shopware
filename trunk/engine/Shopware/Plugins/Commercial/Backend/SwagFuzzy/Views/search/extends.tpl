{block name='frontend_index_content' prepend}
{if $sSearchResults.searchSimilarTerms || $sSearchResults.searchSimilarRequests}
    <div class="fuzzy_result_box">
        <h2 class="headingbox">{s name="SearchSimilarRequests"}Ähnliche Suchbegriffe und verwandte Suchanfragen:{/s}</h2>
        <div class="inner_box">

            {* Similar search queries *}
            {if $sSearchResults.searchSimilarTerms}
                <ul class="first">
                    {foreach from=$sSearchResults.searchSimilarTerms item=similarTerm}
                    <li><a href="{url action=index sSearch=$similarTerm.keyword|escape}">{$similarTerm.keyword|escape}</a></li>
                    {/foreach}
                </ul>
            {/if}

            {* Related search queries *}
            {if $sSearchResults.searchSimilarRequests}
                <ul class="last">
                    {foreach from=$sSearchResults.searchSimilarRequests item=similarRequest}
                    <li><a href="{url action=index sSearch=$similarRequest.searchterm|escape}">{$similarRequest.searchterm|escape} <span>({$similarRequest.results} {s name="SearchSimilarRequestsResults"}Ergebnisse{/s})</span></a></li>
                    {/foreach}
                </ul>
            {/if}
            <div class="clear"></div>
        </div>
    </div>
{/if}
{/block}