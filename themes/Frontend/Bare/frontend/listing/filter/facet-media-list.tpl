{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_media_list"}
    <div class="filter-panel filter--media facet--{$facet->getFacetName()}"
         data-filter-type="media"
         data-field-name="{$facet->getFieldName()}">

        {block name="frontend_listing_filter_facet_media_list_flyout"}
            <div class="filter-panel--flyout">

                {block name="frontend_listing_filter_facet_media_list_title"}
                    <label class="filter-panel--title">
                        {$facet->getLabel()}
                    </label>
                {/block}

                {block name="frontend_listing_filter_facet_media_list_icon"}
                    <span class="filter-panel--icon"></span>
                {/block}

                {block name="frontend_listing_filter_facet_media_list_content"}
                    <div class="filter-panel--content">

                        {block name="frontend_listing_filter_facet_media_list_list"}
                            <ul class="filter-panel--media-list">

                                {foreach $facet->getValues() as $option}

                                    {block name="frontend_listing_filter_facet_media_list_option"}
                                        <li class="filter-panel--media-option">

                                            {block name="frontend_listing_filter_facet_media_list_option_container"}
                                                <div class="option--container">

                                                    {block name="frontend_listing_filter_facet_media_list_input"}
                                                        <input type="checkbox"
                                                               id="__{$facet->getFieldName()}__{$option->getId()}"
                                                               name="__{$facet->getFieldName()}__{$option->getId()}"
                                                               value="{$option->getId()}"
                                                               title="{$option->getLabel()}"
                                                               {if $option->isActive()}checked="checked" {/if}/>
                                                    {/block}

                                                    {block name="frontend_listing_filter_facet_media_list_label"}
                                                        {$mediaFile = {link file='frontend/_public/src/img/no-picture.jpg'}}
                                                        {if $option->getMedia()}
                                                            {$mediaFile = $option->getMedia()->getFile()}
                                                        {/if}

                                                        <label class="filter-panel--media-label"
                                                               for="__{$facet->getFieldName()}__{$option->getId()}">
                                                            <img class="filter-panel--media-image"
                                                                 src="{$mediaFile}"
                                                                 alt="{$option->getLabel()}" />
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