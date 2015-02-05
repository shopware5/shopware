{* Emotion worlds *}
{block name="frontend_listing_list_promotion"}
    {if $hasEmotion}
        {$showListing = false}

        {if $hasEscapedFragment}
            <div class="content--emotions">
                {foreach $emotions as $emotion}
                    {action module=widgets controller=emotion action=index emotionId=$emotion.id controllerName=$Controller}
                {/foreach}
            </div>
        {else}
            {include file="frontend/listing/listing_emotions.tpl"}
        {/if}
    {/if}
{/block}

{* Listing wrapper *}
{block name="frontend_listing_listing_wrapper"}
    <div class="listing--wrapper{if !$showListing} is--hidden{/if}">

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