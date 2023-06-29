{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_boolean"}
    <div class="filter-panel filter--value facet--{$facet->getFacetName()|escapeHtmlAttr}"
         data-filter-type="value"
         data-facet-name="{$facet->getFacetName()|escapeHtmlAttr}"
         data-field-name="{$facet->getFieldName()|escapeHtmlAttr}">

        {block name="frontend_listing_filter_facet_boolean_flyout"}
            <div class="filter-panel--flyout">

                {block name="frontend_listing_filter_facet_boolean_title"}
                    <label class="filter-panel--title" for="{$facet->getFieldName()|escapeHtmlAttr}" title="{$facet->getLabel()|escapeHtmlAttr}">
                        {$facet->getLabel()|escapeHtml}
                    </label>
                {/block}

                {block name="frontend_listing_filter_facet_boolean_checkbox"}
                    <span class="filter-panel--input filter-panel--checkbox">
                        <input type="checkbox"
                               id="{$facet->getFieldName()|escapeHtmlAttr}"
                               name="{$facet->getFieldName()|escapeHtmlAttr}"
                               value="1"
                               {if $facet->isActive()}checked="checked" {/if}/>

                        <span class="input--state checkbox--state">&nbsp;</span>
                    </span>
                {/block}
            </div>
        {/block}
    </div>
{/block}
