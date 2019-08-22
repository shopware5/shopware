{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start'}
    {if $sRequests.sSearchOrginal}
        {s name="SearchResultsFor" assign="snippetSearchResultsFor"}{/s}
        {$sBreadcrumb = [['name' => $snippetSearchResultsFor|htmlentities]]}
    {else}
        {s name="SearchResultsEmpty" assign="snippetSearchResultsEmpty"}{/s}
        {$sBreadcrumb = [['name' => $snippetSearchResultsEmpty]]}
    {/if}
    {$smarty.block.parent}
{/block}

{* Main content *}
{block name='frontend_index_content'}
    <div class="content search--content">

        {block name='frontend_search_info_messages'}
            {if !$sSearchResults.sArticles}
                {if $sRequests.sSearchOrginal}

                    {* No results found *}
                    {block name='frontend_search_message_no_results'}
                        {s name="SearchFuzzyHeadlineNoResult" assign="snippetSearchFuzzyHeadlineNoResult"}{/s}
                        {include file="frontend/_includes/messages.tpl" type="warning" content=$snippetSearchFuzzyHeadlineNoResult}
                    {/block}
                {else}

                    {* Given search term is too short *}
                    {block name='frontend_search_message_shortterm'}
                        {s name="SearchFuzzyInfoShortTerm" assign="snippetSearchFuzzyInfoShortTerm"}{/s}
                        {include file="frontend/_includes/messages.tpl" type="error" content=$snippetSearchFuzzyInfoShortTerm}
                    {/block}
                {/if}
            {/if}
        {/block}

        {if $sSearchResults.sArticles}

            {* Listing varibles *}
            {block name="frontend_search_variables"}
                {$sArticles = $sSearchResults.sArticles}
                {$sNumberArticles = $sSearchResults.sArticlesCount}
                {$sTemplate = "listing"}
                {$sBoxMode = "table"}
                {$showListing = true}
                {$pages = ceil($sNumberArticles / $criteria->getLimit())}
                {$countCtrlUrl = "{url module="widgets" controller="listing" action="listingCount" params=$ajaxCountUrlParams fullPath}"}
            {/block}

            {block name='frontend_search_headline'}
                <h1 class="search--headline">
                    {s name='SearchHeadline'}{/s}
                </h1>
            {/block}

            {block name="frontend_search_sidebar"}
                {include file='frontend/listing/sidebar.tpl'}
            {/block}

            {block name="frontend_search_results"}
                <div class="search--results">
                    {include file='frontend/listing/listing.tpl'}
                </div>
            {/block}
        {/if}
    </div>
{/block}
