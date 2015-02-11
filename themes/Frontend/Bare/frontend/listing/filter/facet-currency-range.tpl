{extends file="parent:frontend/listing/filter/facet-range.tpl"}

{block name="frontend_listing_filter_facet_range_format_helper"}
    <div class="range-slider--currency" data-range-currency="{'0'|currency}"></div>
{/block}

{block name="frontend_listing_filter_facet_range_label_min"}
    <label class="range-info--label"
           for="{$facet->getMinFieldName()}"
           data-range-label="min">
        {$startMin|currency}
    </label>
{/block}

{block name="frontend_listing_filter_facet_range_label_max"}
    <label class="range-info--label"
           for="{$facet->getMaxFieldName()}"
           data-range-label="max">
        {$startMax|currency}
    </label>
{/block}