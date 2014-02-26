{* Promotion *}
{block name="frontend_listing_promotions"}
	{if $sOffers}
		{if !$sTemplate}
			{assign var=sTemplate value="listing-3col"}
		{/if}
		<div class="promotion listing" id="{$sTemplate}">
			{foreach from=$sOffers item=offer}
					{if $offer.mode == "gfx"}
						{include file="frontend/listing/promotion_image.tpl" sArticle=$offer}
					{elseif $offer.mode == "livefix" || $offer.mode == "liverand" || $offer.mode == "liverandcat"}
						{include file='frontend/listing/promotion_liveshopping.tpl' liveArt=$offer.liveshoppingData}
					{else}
						{include file="frontend/listing/promotion_article.tpl" sArticle=$offer}
					{/if}
			{/foreach}
		</div>
	{else}
		{if !$sSupplierInfo && !$sSupplierInfo.id && $hasEmotion}
			{action module=widgets controller=emotion action=index categoryId=$sCategoryContent.id controllerName=$Controller}
		{/if}
	{/if}
{/block}