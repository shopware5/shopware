{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_radio"}
    <div class="filter-panel filter--radio facet--{$facet->getFacetName()|escape:'htmlall'}"
         data-filter-type="radio"
         data-field-name="{$facet->getFieldName()|escape:'htmlall'}">

        {block name="frontend_listing_filter_facet_radio_flyout"}
            <div class="filter-panel--flyout">

                {block name="frontend_listing_filter_facet_radio_title"}
                    <label class="filter-panel--title" for="{$facet->getFieldName()|escape:'htmlall'}">
                        {$facet->getLabel()|escape}
                    </label>
                {/block}

                {block name="frontend_listing_filter_facet_radio_icon"}
                    <span class="filter-panel--icon"></span>
                {/block}

                {block name="frontend_listing_filter_facet_radio_content"}
                    <div class="filter-panel--content">

                        {block name="frontend_listing_filter_facet_radio_list"}
                            <ul class="filter-panel--option-list">

                                {foreach $facet->getValues() as $option}

                                    {block name="frontend_listing_filter_facet_radio_list_option"}
                                        <li class="filter-panel--option">

                                            {block name="frontend_listing_filter_facet_radio_list_option_container"}
                                                <div class="option--container">

                                                    {block name="frontend_listing_filter_facet_radio_list_input"}
                                                        <span class="filter-panel--radio">
                                                            <input type="radio"
                                                                   id="__{$facet->getFieldName()|escape:'htmlall'}__{$option->getId()|escape:'htmlall'}"
                                                                   name="{$facet->getFieldName()|escape:'htmlall'}"
                                                                   value="{$option->getId()|escape:'htmlall'}"
                                                                   {if $option->isActive()}checked="checked" {/if}/>

                                                            <span class="radio--state">&nbsp;</span>
                                                        </span>
                                                    {/block}

                                                    {block name="frontend_listing_filter_facet_radio_list_label"}
                                                        <label class="filter-panel--label"
                                                               for="__{$facet->getFieldName()|escape:'htmlall'}__{$option->getId()|escape:'htmlall'}">
                                                            {$option->getLabel()|escape} &nbsp;
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
