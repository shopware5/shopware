{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_date_config"}
    {$enableTime = 'false'}
{/block}

{block name="frontend_listing_filter_facet_date"}
    <div class="filter-panel filter--date filter-facet--date facet--{$facet->getFacetName()|escapeHtmlAttr}"
         data-filter-type="date"
         data-facet-name="{$facet->getFacetName()|escapeHtmlAttr}"
         data-field-name="{$facet->getFacetName()|escapeHtmlAttr}">

        {block name="frontend_listing_filter_facet_date_flyout"}
            <div class="filter-panel--flyout">

                {block name="frontend_listing_filter_facet_date_title"}
                    <label class="filter-panel--title" for="{$facet->getFieldName()|escapeHtmlAttr}" title="{$facet->getLabel()|escapeHtmlAttr}">
                        {$facet->getLabel()|escapeHtml}
                    </label>
                {/block}

                {block name="frontend_listing_filter_facet_date_icon"}
                    <span class="filter-panel--icon"></span>
                {/block}

                {block name="frontend_listing_filter_facet_date_content"}
                    <div class="filter-panel--content input-type--date">

                        {$value = ''}

                        {foreach $facet->getValues() as $option}
                            {if $option->isActive()}
                                {$value = $option->getId()}
                            {/if}

                            {$enabledDates = "{if $enabledDates}{$enabledDates}, {/if}{$option->getId()}"}
                        {/foreach}

                        {block name="frontend_listing_filter_facet_date_input"}
                            <input type="text"
                                   class="filter-panel--input"
                                   name="{$facet->getFieldName()|escapeHtmlAttr}"
                                   id="{$facet->getFieldName()|escapeHtmlAttr}"
                                   placeholder="{s name="datePickerInputPlaceholder" namespace="frontend/index/datepicker"}{/s}"
                                   data-datepicker="true"
                                   data-mode="single"
                                   data-enableTime="{$enableTime}"
                                   data-enabledDates="{$enabledDates}"
                                   data-static="true"
                                   readonly="readonly"
                                   value="{$value}" />
                        {/block}
                    </div>
                {/block}
            </div>
        {/block}
    </div>
{/block}
