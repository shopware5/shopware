{* Emotion worlds *}
{block name="frontend_listing_list_promotion"}
    {if !$sSupplierInfo && !$sSupplierInfo.id && $hasEmotion}
        {action module=widgets controller=emotion action=index categoryId=$sCategoryContent.id controllerName=$Controller}
    {/if}
{/block}

{* Sorting and changing layout *}
{block name="frontend_listing_top_actions"}
    {if $showListing && !$sOffers}
        {include file='frontend/listing/listing_actions.tpl'}
    {/if}
{/block}

{* Hide actual listing if a emotion world is active *}
{if !$sOffers}
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

                    {block name="frontend_listing_list_inline"}
                        {* Actual listing *}
                        {if $showListing}
                            {foreach $sArticles as $sArticle}
                                {include file="frontend/listing/box_article.tpl"}
                            {/foreach}
                        {/if}
                    {/block}
                </div>
            {/block}
        </div>
{/block}
{else}
    {if $sCategoryContent.parent != 1}
		<a href="{url controller='cat' sPage=1 sCategory=$sCategoryContent.id}">
			{s name="ListingActionsOffersLink"}Weitere Artikel in dieser Kategorie &raquo;{/s}
		</a>
    {/if}
{/if}

{* Paging *}
{block name="frontend_listing_bottom_paging"}
	{if $showListing}
		<div class="listing--bottom-paging">
			{include file="frontend/listing/actions/action-pagination.tpl"}
		</div>
	{/if}
{/block}
