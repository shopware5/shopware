{namespace name=backend/search/index}

{block name="backend/search/index"}
    <div class="search-wrapper">
        <div class="arrow-top"></div>
        {block name="backend/search/index/result"}
            {if !$searchResult.articles && !$searchResult.customers && !$searchResult.orders}
                {block name="backend/search/index/result_empty_header"}
                    <div class="header">
                        <div class="inner">
                            {s name="title/empty_search"}No search results{/s}
                        </div>
                    </div>
                {/block}

                {block name="backend/search/index/result_empty_content"}
                    <div class="result-container">
                        <div class="empty">{s name="item/empty"}No search results{/s}</div>
                    </div>
                {/block}
            {else}
                {if $searchResult.articles}
                    {include file="backend/search/articles.tpl"}
                {/if}
                {if $searchResult.customers}
                    {include file="backend/search/customers.tpl"}
                {/if}
                {if $searchResult.orders}
                    {include file="backend/search/orders.tpl"}
                {/if}
            {/if}
        {/block}
    </div>
{/block}
