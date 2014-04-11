{* Listing actions *}
{block name='frontend_listing_actions_top'}
    <div class="listing--actions block-group{block name='frontend_listing_actions_class'}{/block}">

		{* Filter action button *}
		{block name="frontend_listing_actions_filter"}
			{include file="frontend/listing/actions/action-filter.tpl"}
		{/block}

        {* Order by selection *}
        {block name='frontend_listing_actions_sort'}
            {include file="frontend/listing/actions/action-sorting.tpl"}
        {/block}

		{* Layout switcher *}
		{block name="frontend_listing_actions_change_layout"}
			{include file="frontend/listing/actions/action-change-layout.tpl"}
		{/block}

        {* Products per page selection *}
        {block name='frontend_listing_actions_items_per_page'}
            {include file="frontend/listing/actions/action-per-page.tpl"}
        {/block}

		{* Filter options *}
		{block name="frontend_listing_actions_filter_options"}
			{include file="frontend/listing/actions/action-filter-options.tpl"}
		{/block}

        {* Listing pagination *}
        {block name='frontend_listing_actions_paging'}
            {include file="frontend/listing/actions/action-pagination.tpl"}
        {/block}

        {block name="frontend_listing_actions_close"}{/block}
    </div>
{/block}