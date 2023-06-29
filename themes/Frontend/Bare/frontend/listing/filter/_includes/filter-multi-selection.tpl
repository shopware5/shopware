{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_multi_selection"}
    <div class="filter-panel filter--multi-selection filter-facet--{$filterType} facet--{$facet->getFacetName()|escapeHtmlAttr}"
         data-filter-type="{$filterType}"
         data-facet-name="{$facet->getFacetName()|escapeHtmlAttr}"
         data-field-name="{$facet->getFieldName()|escapeHtmlAttr}">

        {block name="frontend_listing_filter_facet_multi_selection_flyout"}
            <div class="filter-panel--flyout">

                {block name="frontend_listing_filter_facet_multi_selection_title"}
                    <label class="filter-panel--title" for="{$facet->getFieldName()|escapeHtmlAttr}" title="{$facet->getLabel()|escapeHtmlAttr}">
                        {$facet->getLabel()|escapeHtml}
                    </label>
                {/block}

                {block name="frontend_listing_filter_facet_multi_selection_icon"}
                    <span class="filter-panel--icon"></span>
                {/block}

                {block name="frontend_listing_filter_facet_multi_selection_content"}
                    {$inputType = 'checkbox'}

                    {if $filterType == 'radio'}
                        {$inputType = 'radio'}
                    {/if}

                    {$indicator = $inputType}

                    {$isMediaFacet = false}
                    {if $facet|is_a:'\Shopware\Bundle\SearchBundle\FacetResult\MediaListFacetResult'}
                        {$isMediaFacet = true}

                        {$indicator = 'media'}
                    {/if}

                    <div class="filter-panel--content input-type--{$indicator}">

                        {block name="frontend_listing_filter_facet_multi_selection_list"}
                            <ul class="filter-panel--option-list">

                                {foreach $facet->getValues() as $option}

                                    {block name="frontend_listing_filter_facet_multi_selection_option"}
                                        <li class="filter-panel--option">

                                            {block name="frontend_listing_filter_facet_multi_selection_option_container"}
                                                <div class="option--container">

                                                    {block name="frontend_listing_filter_facet_multi_selection_input"}
                                                        <span class="filter-panel--input filter-panel--{$inputType}">
                                                            {$name = "__{$facet->getFieldName()|escapeHtmlAttr}__{$option->getId()|escapeHtmlAttr}"}
                                                            {if $filterType == 'radio'}
                                                                {$name = {$facet->getFieldName()|escapeHtmlAttr} }
                                                            {/if}

                                                            <input type="{$inputType}"
                                                                   id="__{$facet->getFieldName()|escapeHtmlAttr}__{$option->getId()|escapeHtmlAttr}"
                                                                   name="{$name}"
                                                                   value="{$option->getId()|escapeHtmlAttr}"
                                                                   {if $option->isActive()}checked="checked" {/if}/>

                                                            <span class="input--state {$inputType}--state">&nbsp;</span>
                                                        </span>
                                                    {/block}

                                                    {block name="frontend_listing_filter_facet_multi_selection_label"}
                                                        <label class="filter-panel--label"
                                                               for="__{$facet->getFieldName()|escapeHtmlAttr}__{$option->getId()|escapeHtmlAttr}">

                                                            {if $facet|is_a:'\Shopware\Bundle\SearchBundle\FacetResult\MediaListFacetResult'}
                                                                {$mediaFile = {link file='frontend/_public/src/img/no-picture.jpg'}}
                                                                {if $option->getMedia()}
                                                                    {$mediaFile = $option->getMedia()->getFile()}
                                                                {/if}

                                                                <img class="filter-panel--media-image" src="{$mediaFile}" alt="{$option->getLabel()|escapeHtmlAttr}" />
                                                            {else}
                                                                {$option->getLabel()|escapeHtml}
                                                            {/if}
                                                        </label>
                                                    {/block}
                                                </div>
                                            {/block}
                                        </li>
                                    {/block}
                                {/foreach}
                            </ul>
                        {/block}
                    </div>
                {/block}
            </div>
        {/block}
    </div>
{/block}
