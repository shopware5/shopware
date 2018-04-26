{* Paging which will be included in the "listing/listing_actions.tpl" *}
{namespace name="frontend/listing/listing_actions"}

<div class="listing--paging panel--paging">

    {* Pagination label *}
    {block name='frontend_listing_actions_paging_label'}{/block}

    {* Pagination - Frist page *}
    {block name="frontend_listing_actions_paging_first"}
        {if strpos($baseUrl, '?') !== false}
            {assign var='baseUrlParamChar' value='&'}
        {else}
            {assign var='baseUrlParamChar' value='?'}
        {/if}

        {assign var='pagingFirstPage' value="{$baseUrl}{$baseUrlParamChar}p=1"}
        {assign var='pagingPrevPage' value="{$baseUrl}{$baseUrlParamChar}p={$sPage - 1}"}
        {assign var='pagingNextPage' value="{$baseUrl}{$baseUrlParamChar}p={$sPage + 1}"}
        {assign var='pagingLastPage' value="{$baseUrl}{$baseUrlParamChar}p={$sPages}"}

        {if $sPage > 1}
            <a href="{$pagingFirstPage}" title="{"{s name='ListingLinkFirst'}{/s}"|escape}" class="paging--link paging--prev" data-action-link="true">
                <i class="icon--arrow-left"></i>
                <i class="icon--arrow-left"></i>
            </a>
        {/if}
    {/block}

    {* Pagination - Previous page *}
    {block name='frontend_listing_actions_paging_previous'}
        {if $sPage > 1}
            <a href="{$pagingPrevPage}" title="{"{s name='ListingLinkPrevious'}{/s}"|escape}" class="paging--link paging--prev" data-action-link="true">
                <i class="icon--arrow-left"></i>
            </a>
        {/if}
    {/block}

    {* Pagination - current page *}
    {block name='frontend_listing_actions_paging_numbers'}
        {if $pages > 1}
            <a title="{$sCategoryContent.name|escape}" class="paging--link is--active">{$sPage}</a>
        {/if}
    {/block}

    {* Pagination - Next page *}
    {block name='frontend_listing_actions_paging_next'}
        {if $sPage < $pages}
            <a href="{$pagingNextPage}" title="{"{s name='ListingLinkNext'}{/s}"|escape}" class="paging--link paging--next" data-action-link="true">
                <i class="icon--arrow-right"></i>
            </a>
        {/if}
    {/block}

    {* Pagination - Last page *}
    {block name="frontend_listing_actions_paging_last"}
        {if $sPage < $pages}
            <a href="{$pagingLastPage}" title="{"{s name='ListingLinkLast'}{/s}"|escape}" class="paging--link paging--next" data-action-link="true">
                <i class="icon--arrow-right"></i>
                <i class="icon--arrow-right"></i>
            </a>
        {/if}
    {/block}

    {* Pagination - Number of pages *}
    {block name='frontend_listing_actions_count'}
        {if $pages > 1}
            <span class="paging--display">
                {s name="ListingTextFrom"}{/s} <strong>{$pages}</strong>
            </span>
        {/if}
    {/block}

    {* Products per page selection *}
    {block name='frontend_listing_actions_items_per_page'}
        {include file="frontend/listing/actions/action-per-page.tpl"}
    {/block}
</div>