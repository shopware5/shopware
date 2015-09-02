
{extends file='frontend/checkout/cart_header.tpl'}

{* Article price *}
{block name='frontend_checkout_cart_header_price'}<div class="grid_6">&nbsp;</div>{/block}

{* Delivery informations *}
{block name='frontend_checkout_cart_header_availability'}{/block}

{* Article amount *}
{block name='frontend_checkout_cart_header_quantity'}{/block}

{* Article total sum *}
{block name='frontend_checkout_cart_header_total'}
<div class="grid_2 push_4">
	<div class="textright">
		{s name="CartColumnTotal"}{/s}
	</div>
</div>
{/block}
