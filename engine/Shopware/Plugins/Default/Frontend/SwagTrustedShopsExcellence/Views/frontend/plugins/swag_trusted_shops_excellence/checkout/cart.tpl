{block name='frontend_checkout_error_messages_basket_error' append}
	{if $sTsArticleRemoved}
		<div class="error center bold">
			{se namespace="frontend/checkout" name="CartTrustedShopArticleRemoved"}Der Käuferschutz Artikel von Trusted Shop wurde entfernt da für den aktuellen Warenkorbwert ein anderer Artikel ben&ouml;tigt wird oder der Warenkorbwert das Limit von Trusted Shops überschreitet{/se}
		</div>
	{/if}
{/block}

{* Article picture *}
{block name='frontend_checkout_cart_item_image'}
	{if $sBasketItem.trustedShopArticle}
        <a href="#" class="thumb_image" target="_blank">
            <img src="{link file='frontend/_resources/images/trusted_shops_logo_medium.png'}" />
        </a>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{block name='frontend_checkout_cart_item_details'}
	{if $sBasketItem.trustedShopArticle}
		<div class="basket_details">
			{* Article name *}
			<span class="title">{$sBasketItem.articlename|strip_tags|truncate:60}</span>
			<p class="ordernumber">
				{se namespace="frontend/checkout/cart_item" name="CartItemInfoId"}{/se} {$sBasketItem.ordernumber}
			</p>
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
{block name='frontend_checkout_cart_item_delivery_informations'}
    {if $sBasketItem.trustedShopArticle}
        <div class="grid_3"> &nbsp;</div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
{block name='frontend_checkout_cart_item_quantity'}
	{if $sBasketItem.trustedShopArticle}
		<div class="grid_1">&nbsp;</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{block name='frontend_checkout_cart_item_delete_article'}
	{if $sBasketItem.trustedShopArticle}
		<div class="action">
			<a href="{url action='deleteArticle' sDelete=$sBasketItem.id sTargetAction=$sTargetAction}" class="del" title="{s name='CartItemLinkDelete' namespace='frontend/checkout/cart_item'}{/s}">
				&nbsp;
			</a>
			&nbsp;
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}


{* extend the basket *}
{block name='frontend_checkout_cart_cart_head' prepend}
	{include file='frontend/plugins/swag_trusted_shops_excellence/checkout/trusted_shop_row.tpl'}
{/block}

{* extend the basket *}
{block name='frontend_checkout_confirm_shipping' append}
	{include file='frontend/plugins/swag_trusted_shops_excellence/checkout/trusted_shop_row.tpl'}
{/block}

