{if $facet->getLabel()}
    <h3 class="filter--set-title">{$facet->getLabel()}</h3>
{/if}
{include file="frontend/listing/actions/action-filter-facets.tpl" facets=$facet->getFacetResults()}