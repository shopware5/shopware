{namespace name="frontend/listing/box_article"}

<div class="product--actions">
    {block name='frontend_listing_box_article_actions_inner'}

		<div class="product--actions-inner">

        {* Buy now button *}
        {block name='frontend_listing_box_article_actions_buy_now'}
            {if !$sArticle.priceStartingFrom &&!$sArticle.sConfigurator && !$sArticle.variants && !$sArticle.sVariantArticle && !$sArticle.laststock == 1 && !($sArticle.notification == 1 && {config name="deactivatebasketonnotification"} == 1)}
                <a href="{url controller='checkout' action='addArticle' sAdd=$sArticle.ordernumber}"
                   title="{s name='ListingBoxLinkBuy'}{/s}" class="product--action action--buynow btn btn--secondary">
					{s name='ListingBoxLinkBuy'}{/s}
					<i class="icon--arrow-right is--right is--small"></i>
				</a>
            {/if}
        {/block}

        {* More information button *}
        {block name='frontend_listing_box_article_actions_more'}
            <a href="{$sArticle.linkDetails|rewrite:$sArticle.articleName}" title="{$sArticle.articleName}"
               class="product--action action--more btn btn--primary">
				{s name='ListingBoxLinkDetails'}{/s}
				<i class="icon--arrow-right is--right is--small"></i>
			</a>
        {/block}

		</div>

    {/block}
</div>