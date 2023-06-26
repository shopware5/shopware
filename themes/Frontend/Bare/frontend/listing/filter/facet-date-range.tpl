{extends file="frontend/listing/filter/facet-date.tpl"}

{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_date_title"}
    <label class="filter-panel--title" for="{$facet->getFacetName()|escapeHtmlAttr}" title="{$facet->getLabel()|escapeHtmlAttr}">
        {$facet->getLabel()|escapeHtml}
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
                   id="{$facet->getFacetName()|escapeHtmlAttr}"
                   placeholder="{$snippetDatePickerInputPlaceholder|escapeHtmlAttr}"
                   data-datepicker="true"
                   data-mode="range"
                   data-enableTime="{$enableTime}"
                   data-minDate="{$rangeMin}"
                   data-maxDate="{$rangeMax}"
                   data-rangeStartInput="{$facet->getMinFieldName()|escapeHtmlAttr}"
                   data-rangeEndInput="{$facet->getMaxFieldName()|escapeHtmlAttr}"
                   data-static="true"
                   readonly="readonly" />
        {/block}

        {block name="frontend_listing_filter_facet_date_range_min_label"}
            <label for="{$facet->getMinFieldName()|escapeHtmlAttr}"
                   data-date-range-label="min"
                   class="is--hidden">{s name="ListingFilterRangeFrom"}{/s}</label>
        {/block}

        {block name="frontend_listing_filter_facet_date_range_min_input"}
            <input type="hidden"
                   data-date-range-input="min"
                   id="{$facet->getMinFieldName()|escapeHtmlAttr}"
                   name="{$facet->getMinFieldName()|escapeHtmlAttr}"
                   value="{if $facet->isActive()}{$startMin}{/if}" />
        {/block}

        {block name="frontend_listing_filter_facet_date_range_max_label"}
            <label for="{$facet->getMaxFieldName()|escapeHtmlAttr}"
                   data-date-range-label="max"
                   class="is--hidden">{s name="ListingFilterRangeTo"}{/s}</label>
        {/block}

        {block name="frontend_listing_filter_facet_date_range_max_input"}
            <input type="hidden"
                   data-date-range-input="max"
                   id="{$facet->getMaxFieldName()|escapeHtmlAttr}"
                   name="{$facet->getMaxFieldName()|escapeHtmlAttr}"
                   value="{if $facet->isActive()}{$startMax}{/if}" />
        {/block}
    </div>
{/block}
