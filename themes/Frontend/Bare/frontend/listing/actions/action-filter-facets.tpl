{**
 * Iteration for the different filter facets.
 * The file is called recursive for deeper structured facet groups.
 *}
{block name="frontend_listing_actions"}
    {foreach $facets as $facet}
        {block name="frontend_listing_actions_facet"}
            {if $facet->getTemplate() !== null}
                {include file=$facet->getTemplate() facet=$facet}
            {/if}
        {/block}
    {/foreach}
{/block}
