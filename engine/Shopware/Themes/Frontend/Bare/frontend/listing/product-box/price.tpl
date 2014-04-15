{namespace name="frontend/listing/box_article"}

<div class="product--price price--default{if $sArticle.pseudoprice} price--discount{/if}">

    {* Default price *}
    {block name='frontend_listing_box_article_price_default'}
        <strong class="price--content content--default">
            {if $sArticle.priceStartingFrom && !$sArticle.liveshoppingData}{s name='ListingBoxArticleStartsAt'}{/s} {/if}{$sArticle.price|currency} {s name="Star"}*{/s}
        </strong>
    {/block}

	{* Discount price *}
	{block name='frontend_listing_box_article_price_discount'}
		{if $sArticle.pseudoprice}

			{* Discount price content *}
			{block name='frontend_listing_box_article_price_discount_content'}
				<strong class="price--content content--discount">
					{s name="reducedPrice"}Statt: {/s} {$sArticle.pseudoprice|currency} {s name="Star"}*{/s}
				</strong>
			{/block}
		{/if}
	{/block}
</div>