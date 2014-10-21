{namespace name="frontend/listing/listing"}

<div class="listing--container">
    <ul class="listing listing--{$sTemplate}"{if $theme.infiniteScrolling} data-infinite-scrolling="true" data-loadPreviousSnippet="{s name="ListingActionsLoadPrevious"}Vorherige Artikel laden{/s}" data-loadMoreSnippet="{s name="ListingActionsLoadMore"}Weitere Artikel laden{/s}" data-categoryId="{$sCategoryContent.id}" data-pages="{$pages}" data-threshold="{$theme.infiniteThreshold}"{/if}>
        {block name="frontend_listing_list_inline"}
            {* Actual listing *}
            {if $showListing}
                {foreach $sArticles as $sArticle}
                    {include file="frontend/listing/box_article.tpl" sTemplate=$sTemplate lastitem=$sArticle@last firstitem=$sArticle@first}
                {/foreach}
            {/if}
        {/block}
    </ul>
</div>