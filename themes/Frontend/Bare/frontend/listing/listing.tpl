{* Emotion worlds *}
{block name="frontend_listing_list_promotion"}
    {if $hasEmotion}
        {$fullscreen = false}

        {block name="frontend_listing_emotions"}
            <div class="content--emotions">

                {foreach $emotions as $emotion}
                    {if $emotion.fullscreen == 1}
                        {$fullscreen = true}
                    {/if}

                    <div class="emotion--wrapper"
                         data-controllerUrl="{url module=widgets controller=emotion action=index emotionId=$emotion.id controllerName=$Controller}"
                         data-availableDevices="{$emotion.devices}"
                         data-showListing="{if $showListing == 1}true{else}false{/if}">
                    </div>
                {/foreach}

                {if !$showListing}
                    {block name="frontend_listing_list_promotion_link_show_listing"}
                        <div class="emotion--show-listing{if $fullscreen} is--align-center{/if}">
                            <a href="{url controller='cat' sPage=1 sCategory=$sCategoryContent.id}" title="{$sCategoryContent.name|escape}" class="link--show-listing{if $fullscreen} btn is--primary{/if}">
                                {s name="ListingActionsOffersLink"}Weitere Artikel in dieser Kategorie &raquo;{/s}
                            </a>
                        </div>
                    {/block}
                {/if}
            </div>
        {/block}
    {/if}
{/block}

{* Listing wrapper *}
{block name="frontend_listing_listing_wrapper"}
    {if $showListing}

        {$listingCssClass = "listing--wrapper"}
        {$deviceClasses = [0 => 'xl', 1 => 'l', 2 => 'm', 3 => 's', 4 => 'xs']}

        {foreach $showListingDevices as $device}
            {$listingCssClass = "{$listingCssClass} listing--{$deviceClasses[$device]}"}
        {/foreach}

        <div class="{$listingCssClass}">

            {* Sorting and changing layout *}
            {block name="frontend_listing_top_actions"}
                {include file='frontend/listing/listing_actions.tpl'}
            {/block}

            {block name="frontend_listing_listing_container"}
                <div class="listing--container">

                    {block name="frontend_listing_listing_content"}
                        <div class="listing"
                             data-ajax-wishlist="true"
                             data-compare-ajax="true"
                             {if $theme.infiniteScrolling}
                                data-infinite-scrolling="true"
                                data-loadPreviousSnippet="{s name="ListingActionsLoadPrevious"}{/s}"
                                data-loadMoreSnippet="{s name="ListingActionsLoadMore"}{/s}"
                                data-categoryId="{$sCategoryContent.id}"
                                data-pages="{$pages}"
                                data-threshold="{$theme.infiniteThreshold}"
                            {/if}>

                            {* Actual listing *}
                            {block name="frontend_listing_list_inline"}
                                {foreach $sArticles as $sArticle}
                                    {include file="frontend/listing/box_article.tpl"}
                                {/foreach}
                            {/block}
                        </div>
                    {/block}
                </div>
            {/block}

            {* Paging *}
            {block name="frontend_listing_bottom_paging"}
                <div class="listing--bottom-paging">
                    {include file="frontend/listing/actions/action-pagination.tpl"}
                </div>
            {/block}
        </div>
    {/if}
{/block}