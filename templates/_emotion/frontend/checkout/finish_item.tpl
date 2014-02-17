
{extends file='frontend/checkout/cart_item.tpl'}

{* Article price *}
{block name='frontend_checkout_cart_item_price'}<div class="grid_6">&nbsp;</div>{/block}

{* Delivery informations *}
{block name='frontend_checkout_cart_item_delivery_informations'}{/block}

{* Article amount *}
{block name='frontend_checkout_cart_item_quantity'}{/block}

{block name='frontend_checkout_cart_item_delete_article'}{/block}
{block name='frontend_checkout_cart_item_voucher_delete'}{/block}
{block name='frontend_checkout_cart_item_premium_delete'}{/block}

{* Article total sum *}
{block name='frontend_checkout_cart_item_total_sum'}
<div class="grid_2 push_4">
	<div class="textright">
		<strong>
			{$sBasketItem.amount|currency}*
		</strong>
	</div>
</div>
{/block}

{* Voucher price *}
{block name='frontend_checkout_cart_item_voucher_price'}
<div class="grid_3 push_9">
	<div class="textright">
		<strong>
		{if $sBasketItem.itemInfo}
			{$sBasketItem.itemInfo}
		{else}
			{$sBasketItem.price|currency}*
		{/if}
		</strong>
	</div>
</div>
{/block}

{* Basket rebate price *}
{block name='frontend_checkout_cart_item_rebate_price'}
<div class="grid_3 push_9">
	<div class="textright">
		<strong>
			{if $sBasketItem.itemInfo}
				{$sBasketItem.itemInfo}
			{else}
				{$sBasketItem.price|currency}*
			{/if}
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

{* Premium price *}
{block name='frontend_checkout_cart_item_premium_price'}
<div class="grid_3 push_9">
	<div class="textright">
		<strong>
			{s name="CartItemInfoFree"}{/s}
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

{* Extra charge price *}
{block name='frontend_checkout_Cart_item_small_quantities_price'}
<div class="grid_3 push_9">
	<div class="textright">
		<strong>
			{if $sBasketItem.itemInfo}
				{$sBasketItem.itemInfo}
			{else}
				{$sBasketItem.price|currency}*
			{/if}
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

{* Bundle discount price *}
{block name='frontend_checkout_cart_item_bundle_price'}
<div class="grid_3 push_9">
	<div class="textright">
		<strong>
			{$sBasketItem.amount|currency}*
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

