
<div class="inner_cart">
	{if $sBasket.content}
		{foreach name=ajaxbasket from=$sBasket.content item=sBasketItem}
			
			{block name='frontend_checkout_ajax_cart_row'}
			
			<div class="{if $sBasketItem.modus == 1} premium{elseif $sBasketItem.modus == 10} bundle{/if}{if $smarty.foreach.ajaxbasket.last} last{/if}">
				{if $sBasketItem.image.src.0}
				<div class="thumbnail">
					<img src="{$sBasketItem.image.src.0}" alt="{$sBasketItem.articlename|strip_tags}" />
				</div>
				{/if}
				
				{* Article name *}
				{block name='frontend_checkout_ajax_cart_articlename'}
				<span class="title">
					<strong>{$sBasketItem.quantity}x</strong> <a href="{$sBasketItem.linkDetails}" title="{$sBasketItem.articlename|strip_tags}">
					{if $sBasketItem.modus == 10}{se name='AjaxCartInfoBundle'}{/se}{else}{$sBasketItem.articlename|truncate:30}{/if}
					</a>
				</span>
				{/block}
				
				{block name='frontend_checkout_ajax_cart_price'}
				{* Article price *}
				<strong class="price">{if $sBasketItem.amount}{$sBasketItem.amount|currency}{else}{se name="AjaxCartInfoFree"}{/se}{/if}*</strong>
				{/block}
				
				
			</div>
			{/block}
		{/foreach}
	{else}
		{block name='frontend_checkout_ajax_cart_empty'}
		<div class="{if !$sBasket.content}last{/if}">
			{se name='AjaxCartInfoEmpty'}{/se}
		</div>
		{/block}
	{/if}
</div>
{* Basket link *}
{block name='frontend_checkout_ajax_cart_open_basket'}
<div class="left">
	<a href="{url controller='checkout' action='cart'}" class="button-left small_left" title="{s name='AjaxCartLinkBasket'}{/s}">
		{se name='AjaxCartLinkBasket'}{/se}
	</a>
</div>
<div class="right">
<a href="{url controller='checkout' action='confirm'}" class="button-right small_right checkout" title="{s name='AjaxCartLinkConfirm'}{/s}">
		{se name='AjaxCartLinkConfirm'}{/se}
	</a>
</div>
<div class="clear"></div>
{/block}
