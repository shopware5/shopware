{extends file='parent:frontend/checkout/cart_item.tpl'}

{block name='frontend_checkout_cart_item_voucher_details'}
	<div class="voucher_img">&nbsp;</div>
	<div class="basket_details">
		<strong class="title">{$sBasketItem.articlename}</strong>
		
		<p class="ordernumber">
		{se name="CartItemInfoId"}{/se}: {$sBasketItem.ordernumber}
		</p>
	</div>
{/block}

{block name='frontend_checkout_cart_item_premium_image'}
	<span class="premium_img">
		{se name="sCartItemFree"}GRATIS!{/se}
	</span>
{/block}

