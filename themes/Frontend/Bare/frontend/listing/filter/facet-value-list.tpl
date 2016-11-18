{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_value_list"}
    {$type = 'value-list'}
    {if {config name="generatePartialFacets"} }
        {$type = 'value-list-single'}
    {/if}

    {include file='frontend/listing/filter/_includes/filter-multi-selection.tpl' filterType=$type}
{/block}