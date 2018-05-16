{namespace name="frontend/listing/listing_actions"}
{**
 * Iteration for the different filter facets.
 * The file is called recursive for deeper structured facet groups.
 *}
{block name="frontend_listing_filter_facet"}
    {foreach $facets as $facet}
        {if $facet->getTemplate() !== null}
            {block name="frontend_listing_filter_facet_call"}
                {include file=$facet->getTemplate() facet=$facet}
            {/block}
        {/if}
    {/foreach}
{/block}
