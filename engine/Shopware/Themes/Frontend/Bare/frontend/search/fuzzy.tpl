{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' prepend}
    {$sBreadcrumb = [['name'=>"{s name="SearchResultsFor"}Suchergebnis für {$sRequests.sSearch}{/s}", 'link'=>{url}]]}
{/block}

{* Sidebar left *}
{block name='frontend_index_content_left'}{/block}

{* Main content *}
{block name='frontend_index_content'}
<div class="content search--results">

    {if !$sSearchResults.sArticles}
        {if $sRequests.sSearchOrginal}
            {* No results found *}
            {block name='frontend_search_fuzzy_empty'}
                <div class="alert is--error is--rounded">
                    <div class="alert--icon">
                        <i class="icon--element icon--info"></i>
                    </div>
                    <div class="alert--content">
                        {s name='SearchFuzzyHeadlineEmpty'}{/s}
                    </div>
                </div>
            {/block}
        {else}

            {* Given search term is too short *}
            {block name='frontend_search_fuzzy_shortterm'}
                <div class="alert is--error is--rounded">
                    <div class="alert--icon">
                        <i class="icon--element icon--info"></i>
                    </div>
                    <div class="alert--content">
                        {s name='SearchFuzzyInfoShortTerm'}{/s}
                    </div>
                </div>
            {/block}
        {/if}
    {/if}

    {if $sSearchResults.sArticles}
        {* Results count headline *}
        {block name='frontend_search_fuzzy_headline'}
            <h1>{s name='SearchHeadline'}Zu "{$sRequests.sSearch}" wurden {$sSearchResults.sArticlesCount} Artikel gefunden{/s}</h1>
        {/block}

        {* Search reults filter elements *}
        {block name="frontend_search_fuzzy_filter"}
            {include file='frontend/search/fuzzy-filter.tpl'}
        {/block}

        {* Sorting and changing layout *}
        {block name="frontend_search_fuzzy_actions"}
            <div class="results--paging panel">
                {include file='frontend/search/fuzzy-paging.tpl' sTemplate=$sTemplate sAdvancedActions=1}
            </div>
        {/block}

        {* Search results listing *}
        {block name="frontend_search_fuzzy_results"}
            <div class="results--articles panel">
                <ul class="listing listing--{if $sRequests.sTemplate eq 'list'}listing-2col{else}listing{/if}">
                    {foreach $sSearchResults.sArticles as $key => $sArticle}
                        {include file='frontend/listing/box_article.tpl'}
                    {/foreach}
                </ul>
            <div>
        {/block}
    {/if}
</div>
{/block}