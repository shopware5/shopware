{block name='frontend_checkout_cart_item_delete_article'}{/block}

{* Article price *}
{block name='frontend_checkout_cart_item_price'}
	{if $sBasketItem.trustedShopArticle && !$isEmotionTemplate}
		<div class="grid_2">&nbsp;</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
