{* Listing actions *}
{block name='frontend_listing_actions_top'}
    {$listingMode = {config name=listingMode}}

    {block name="frontend_listing_actions_top_hide_detection"}
        {$hide = ($listingMode != 'full_page_reload' && ($theme.sidebarFilter || $sCategoryContent.hideFilter) && $sCategoryContent.hide_sortings)}
    {/block}

    <div data-listing-actions="true"
         {if $listingMode != 'full_page_reload'}data-bufferTime="0"{/if}
         class="listing--actions is--rounded{block name='frontend_listing_actions_class'}{/block}{if $hide} is--hidden{/if}">

        {* Filter action button *}
        {block name="frontend_listing_actions_filter"}
            {include file="frontend/listing/actions/action-filter-button.tpl"}
        {/block}

        {* Order by selection *}
        {block name='frontend_listing_actions_sort'}
            {include file="frontend/listing/actions/action-sorting.tpl"}
        {/block}

        {* Filter options *}
        {block name="frontend_listing_actions_filter_options"}
            {if !$theme.sidebarFilter}
                {include file="frontend/listing/actions/action-filter-panel.tpl"}
            {/if}
        {/block}

        {* Listing pagination *}
        {block name='frontend_listing_actions_paging'}
            {include file="frontend/listing/actions/action-pagination.tpl"}
        {/block}

        {block name="frontend_listing_actions_close"}{/block}
    </div>
{/block}
