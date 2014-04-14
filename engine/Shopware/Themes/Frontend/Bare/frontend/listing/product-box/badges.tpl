{namespace name="frontend/listing/box_article"}

{* Highlight badge *}
{block name='frontend_listing_box_article_hint'}
    {if $sArticle.highlight}
        <div class="product--badge badge--highlight ribbon is--right">
            <div class="ribbon--content green is--uppercase">{s name='ListingBoxTip'}{/s}</div>
        </div>
    {/if}
{/block}

{* Newcomer badge *}
{block name='frontend_listing_box_article_new'}
    {if $sArticle.newArticle}
        <div class="product--badge badge--newcomer ribbon is--right">
			<div class="ribbon--content green is--uppercase">{s name='ListingBoxNew'}{/s}</div>
        </div>
    {/if}
{/block}

{* ESD product badge *}
{block name='frontend_listing_box_article_esd'}
    {if $sArticle.esd}
        <div class="product--badge badge--esd ribbon is--right">
			<div class="ribbon--content orange is--uppercase">{s name='ListingBoxInstantDownload'}{/s}</div>
        </div>
    {/if}
{/block}

{* Discount badge *}
{block name='frontend_listing_box_article_discount'}
    {if $sArticle.pseudoprice}
        <div class="product--badge badge--discount">%</div>
    {/if}
{/block}