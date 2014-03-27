{namespace name="frontend/listing/box_article"}

{* Highlight badge *}
{block name='frontend_listing_box_article_hint'}
    {if $sArticle.highlight}
        <div class="product--badge badge--highlight">
            {s name='ListingBoxTip'}{/s}
        </div>
    {/if}
{/block}

{* Newcomer badge *}
{block name='frontend_listing_box_article_new'}
    {if $sArticle.newArticle}
        <div class="product--badge badge--newcomer">
            {s name='ListingBoxNew'}{/s}
        </div>
    {/if}
{/block}

{* ESD product badge *}
{block name='frontend_listing_box_article_esd'}
    {if $sArticle.esd}
        <div class="product--badge badge--esd">
            {s name='ListingBoxInstantDownload'}{/s}
        </div>
    {/if}
{/block}

{* Discount badge *}
{block name='frontend_listing_box_article_discount'}
    {if $sArticle.pseudoprice}
        <div class="product--badge badge--discount">%</div>
    {/if}
{/block}