{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_range"}
    <div class="filter-panel filter--range facet--{$facet->getFacetName()|escapeHtmlAttr}"
         data-filter-type="range"
         data-facet-name="{$facet->getFacetName()|escapeHtmlAttr}"
         data-field-name="{$facet->getFacetName()|escapeHtmlAttr}">

        {block name="frontend_listing_filter_facet_range_flyout"}
            <div class="filter-panel--flyout">

                {block name="frontend_listing_filter_facet_range_title"}
                    <label class="filter-panel--title" title="{$facet->getLabel()|escapeHtmlAttr}">
                        {$facet->getLabel()|escapeHtml}
                    </label>
                {/block}

                {block name="frontend_listing_filter_facet_range_icon"}
                    <span class="filter-panel--icon"></span>
                {/block}

                {block name="frontend_listing_filter_facet_range_content"}
                    <div class="filter-panel--content">

                        {block name="frontend_listing_filter_facet_range_slider"}

                            {block name="frontend_listing_filter_facet_range_slider_config"}
                                {$startMin = $facet->getActiveMin()}
                                {$startMax = $facet->getActiveMax()}
                                {$rangeMin = $facet->getMin()}
                                {$rangeMax = $facet->getMax()}
                                {$roundPretty = 'false'}
                                {$format = "{'0.00'|number:['precision' => 2]}"}
                                {$suffix = $facet->getSuffix()}
                                {if $facet->getDigits() >= 0}
                                    {$digits = $facet->getDigits()}
                                {/if}
                                {$stepCount = 100}
                                {$stepCurve = 'linear'}
                            {/block}

                            <div class="range-slider"
                                 data-range-slider="true"
                                 data-roundPretty="{$roundPretty}"
                                 data-labelFormat="{$format}"
                                 data-suffix="{$suffix}"
                                 data-stepCount="{$stepCount}"
                                 data-stepCurve="{$stepCurve}"
                                 data-startMin="{$startMin}"
                                 data-digits="{$digits}"
                                 data-startMax="{$startMax}"
                                 data-rangeMin="{$rangeMin}"
                                 data-rangeMax="{$rangeMax}">

                                {block name="frontend_listing_filter_facet_range_input_min"}
                                    <input type="hidden"
                                           id="{$facet->getMinFieldName()|escapeHtmlAttr}"
                                           name="{$facet->getMinFieldName()|escapeHtmlAttr}"
                                           data-range-input="min"
                                           value="{$startMin}" {if !$facet->isActive() || $startMin == 0}disabled="disabled" {/if}/>
                                {/block}

                                {block name="frontend_listing_filter_facet_range_input_max"}
                                    <input type="hidden"
                                           id="{$facet->getMaxFieldName()|escapeHtmlAttr}"
                                           name="{$facet->getMaxFieldName()|escapeHtmlAttr}"
                                           data-range-input="max"
                                           value="{$startMax}" {if !$facet->isActive() || $startMax == 0}disabled="disabled" {/if}/>
                                {/block}

                                {block name="frontend_listing_filter_facet_range_info"}
                                    <div class="filter-panel--range-info">

                                        {block name="frontend_listing_filter_facet_range_info_min"}
                                            <span class="range-info--min">
                                                {s name="ListingFilterRangeFrom"}{/s}
                                            </span>
                                        {/block}

                                        {block name="frontend_listing_filter_facet_range_label_min"}
                                            <label class="range-info--label"
                                                   for="{$facet->getMinFieldName()|escapeHtmlAttr}"
                                                   data-range-label="min">
                                                {$startMin}
                                            </label>
                                        {/block}

                                        {block name="frontend_listing_filter_facet_range_info_max"}
                                            <span class="range-info--max">
                                                {s name="ListingFilterRangeTo"}{/s}
                                            </span>
                                        {/block}

                                        {block name="frontend_listing_filter_facet_range_label_max"}
                                            <label class="range-info--label"
                                                   for="{$facet->getMaxFieldName()|escapeHtmlAttr}"
                                                   data-range-label="max">
                                                {$startMax}
                                            </label>
                                        {/block}
                                    </div>
                                {/block}
                            </div>
                        {/block}
                    </div>
                {/block}
            </div>
        {/block}
    </div>
{/block}
