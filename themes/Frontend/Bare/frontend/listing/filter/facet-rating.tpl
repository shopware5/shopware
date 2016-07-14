{namespace name="frontend/listing/listing_actions"}

{block name="frontend_listing_filter_facet_rating"}
    <div class="filter-panel filter--rating facet--{$facet->getFacetName()|escape:'htmlall'}"
         data-filter-type="rating"
         data-field-name="{$facet->getFieldName()|escape:'htmlall'}">

        {block name="frontend_listing_filter_facet_rating_flyout"}
            <div class="filter-panel--flyout">

                {block name="frontend_listing_filter_facet_rating_title"}
                    <label class="filter-panel--title">
                        {$facet->getLabel()|escape}
                    </label>
                {/block}

                {block name="frontend_listing_filter_facet_rating_icon"}
                    <span class="filter-panel--icon"></span>
                {/block}

                {block name="frontend_listing_filter_facet_rating_content"}
                    <div class="filter-panel--content">

                        {block name="frontend_listing_filter_facet_rating_stars"}
                            <div class="filter-panel--star-rating">

                                {block name="frontend_listing_filter_facet_rating_reset_input"}
                                    <input type="radio"
                                           class="rating-star--input"
                                           id="star--reset"
                                           name="rating"
                                           value=""
                                           {if !$facet->isActive()}checked="checked" {/if}/>
                                {/block}

                                {foreach $facet->getValues() as $value}
                                    {block name="frontend_listing_filter_facet_rating_label"}
                                        <label class="rating-star--label star--{$value->getId()}" for="star--{$value->getId()}"></label>
                                    {/block}

                                    {block name="frontend_listing_filter_facet_rating_input"}
                                        <input type="radio"
                                               class="rating-star--input"
                                               id="star--{$value->getId()}"
                                               name="rating"
                                               value="{$value->getId()}"
                                               {if $value->isActive()}checked="checked" {/if}/>
                                    {/block}
                                {/foreach}
                            </div>
                        {/block}
                    </div>
                {/block}
            </div>
        {/block}
    </div>
{/block}
