{extends file="frontend/listing/filter/facet-date.tpl"}

{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_date_title"}
    <label class="filter-panel--title" for="{$facet->getFacetName()|escape:'htmlall'}" title="{$facet->getLabel()|escape:'htmlall'}">
        {$facet->getLabel()|escape}
    </label>
{/block}

{block name="frontend_listing_filter_facet_date_content"}
    <div class="filter-panel--content input-type--date">

        {$startMin = $facet->getActiveMin()}
        {$startMax = $facet->getActiveMax()}
        {$rangeMin = $facet->getMin()}
        {$rangeMax = $facet->getMax()}

        {block name="frontend_listing_filter_facet_date_range_input"}
            {s name="datePickerInputPlaceholder" namespace="frontend/index/datepicker" assign="snippetDatePickerInputPlaceholder"}{/s}
            <input type="text"
                   class="filter-panel--input"
                   id="{$facet->getFacetName()|escape:'htmlall'}"
                   placeholder="{$snippetDatePickerInputPlaceholder|escape:'htmlall'}"
                   data-datepicker="true"
                   data-mode="range"
                   data-enableTime="{$enableTime}"
                   data-minDate="{$rangeMin}"
                   data-maxDate="{$rangeMax}"
                   data-rangeStartInput="{$facet->getMinFieldName()|escape:'htmlall'}"
                   data-rangeEndInput="{$facet->getMaxFieldName()|escape:'htmlall'}"
                   data-static="true"
                   readonly="readonly" />
        {/block}

        {block name="frontend_listing_filter_facet_date_range_min_label"}
            <label for="{$facet->getMinFieldName()|escape:'htmlall'}"
                   data-date-range-label="min"
                   class="is--hidden">{s name="ListingFilterRangeFrom"}{/s}</label>
        {/block}

        {block name="frontend_listing_filter_facet_date_range_min_input"}
            <input type="hidden"
                   data-date-range-input="min"
                   id="{$facet->getMinFieldName()|escape:'htmlall'}"
                   name="{$facet->getMinFieldName()|escape:'htmlall'}"
                   value="{if $facet->isActive()}{$startMin}{/if}" />
        {/block}

        {block name="frontend_listing_filter_facet_date_range_max_label"}
            <label for="{$facet->getMaxFieldName()|escape:'htmlall'}"
                   data-date-range-label="max"
                   class="is--hidden">{s name="ListingFilterRangeTo"}{/s}</label>
        {/block}

        {block name="frontend_listing_filter_facet_date_range_max_input"}
            <input type="hidden"
                   data-date-range-input="max"
                   id="{$facet->getMaxFieldName()|escape:'htmlall'}"
                   name="{$facet->getMaxFieldName()|escape:'htmlall'}"
                   value="{if $facet->isActive()}{$startMax}{/if}" />
        {/block}
    </div>
{/block}