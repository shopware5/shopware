{**
 * Iteration for the different filter facets.
 * The file is called recursive for deeper structured facet groups.
 *}
{foreach $facets as $facet}

    {**
     * Rating facet
     * Shows a special filter panel for selecting a product rating via small star labels.
     *}
    {if $facet->getFacetName() === 'vote_average'}
        {include file="frontend/listing/filter/facet-rating.tpl" facet=$facet}

    {**
     * Boolean facet
     * Shows a filter panel with a single value which can be activated via checkbox.
     *}
    {elseif $facet|is_a: 'Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult'}
        {include file="frontend/listing/filter/facet-boolean.tpl" facet=$facet}

    {**
     * Range facet
     * Shows a filter panel with a range slider where you can select
     * the min and max value via drag and drop.
     *}
    {elseif $facet|is_a: 'Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult'}
        {include file="frontend/listing/filter/facet-range.tpl" facet=$facet}

    {**
     * Value list facet
     * Shows a filter panel with a list of values, each selectable via checkbox.
     *}
    {elseif $facet|is_a: 'Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult'}
        {include file="frontend/listing/filter/facet-value-list.tpl" facet=$facet}

    {**
     * Media list facet
     * Shows a filter panel with a list of values, each selectable via a small image.
     *}
    {elseif $facet|is_a: 'Shopware\Bundle\SearchBundle\FacetResult\MediaListFacetResult'}
        {include file="frontend/listing/filter/facet-media-list.tpl" facet=$facet}

    {**
     * Radio facet
     * Shows a filter panel with a list of possible values, but only one
     * can be selected via radio buttons.
     *}
    {elseif $facet|is_a: 'Shopware\Bundle\SearchBundle\FacetResult\RadioFacetResult'}
        {include file="frontend/listing/filter/facet-radio.tpl" facet=$facet}

    {**
     * Facet group
     * Will call the file recursive to render each single facet in the group.
     *}
    {elseif $facet|is_a: 'Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup'}
        <h3 class="filter--set-title">{$facet->getLabel()}</h3>
        {include file="frontend/listing/actions/action-filter-facets.tpl" facets=$facet->getFacetResults()}
    {/if}
{/foreach}