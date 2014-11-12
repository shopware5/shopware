{* Emotion worlds *}
{block name="frontend_listing_list_promotion"}
    {if $hasEmotion}
        {$showListing = false}

        <div class="content--emotions">
            {foreach $emotions as $emotion}

                {if $emotion.showListing == 1}
                    {$showListing = true}
                {/if}

                <div class="emotion--wrapper"
                     data-controllerUrl="{url module=widgets controller=emotion action=index emotionId=$emotion.id controllerName=$Controller}"
                     data-availableDevices="{$emotion.devices}"
                     data-showListing="{if $emotion.showListing == 1}true{else}false{/if}">
                </div>
            {/foreach}

            {block name="frontend_listing_list_promotion_link_show_listing"}
                {if !$showListing}
                    <div class="emotion--show-listing">
                        <a href="{url controller='cat' sPage=1 sCategory=$sCategoryContent.id}" class="link--show-listing">
                            {s name="ListingActionsOffersLink"}Weitere Artikel in dieser Kategorie &raquo;{/s}
                        </a>
                    </div>
                {/if}
            {/block}
        </div>
    {/if}
{/block}

{* Listing wrapper *}
{block name="frontend_listing_listing_wrapper"}
    <div class="listing--wrapper">

        {* Sorting and changing layout *}
        {block name="frontend_listing_top_actions"}
            {include file='frontend/listing/listing_actions.tpl'}
        {/block}

        {block name="frontend_listing_listing_container"}
            <div class="listing--container">

                {block name="frontend_listing_listing_content"}
                    <div class="listing" 
                        data-ajax-wishlist="true"
                        {if $theme.infiniteScrolling}
                        data-infinite-scrolling="true"
                        data-loadPreviousSnippet="{s name="ListingActionsLoadPrevious"}Vorherige Artikel laden{/s}"
                        data-loadMoreSnippet="{s name="ListingActionsLoadMore"}Weitere Artikel laden{/s}"
                        data-categoryId="{$sCategoryContent.id}"
                        data-pages="{$pages}"
                        data-threshold="{$theme.infiniteThreshold}"{/if}>

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
{/block}