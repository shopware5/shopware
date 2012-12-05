{* display how much points the user will get for this basket content *}
{block name='frontend_checkout_confirm_payment' prepend}
	<div class="points_for_basket">
	<h2>{se namespace="frontend/bonus_system" name="BonusSystemBasketHeadline"}Ihre Bonuspunkte{/se}</h2>
		{if $sUserLoggedIn}
		<div class="basket-points_left">
			<div class="image">P</div>
			<p class="spending_top">
				{se namespace="frontend/bonus_system" name="BonusSystemBasketBottomSpendingPoints"}Punkte eingel&ouml;st{/se}
			</p>
			<p class="spending_bottom">
				-{$sBonusSystem.points.spending}
			</p>
		</div>
		{/if}
		
		<div class="basket-points_right">
		<p class="earning_top">
			{se namespace="frontend/bonus_system" name="BonusSystemBasketBottomEarningPoints"}Punkte f&uuml;r die Bestellung{/se}
		</p>
		<p class="earning_bottom">
			+{$sBonusSystem.points.earning}
		</p>
		</div>
		<div class="clear">&nbsp;</div>
	</div>
{/block}

{* don't display inline details for bonus articles *}
{block name='frontend_checkout_cart_item_details_inline'}
	{if !$sBasketItem.isBonusArticle}
		{$smarty.block.parent}
	{else}
	{/if}
{/block}

{* don't display tax prices of the article for bonus articles *}
{block name='frontend_checkout_cart_item_tax_price'}
	{if !$sBasketItem.isBonusArticle}
		{$smarty.block.parent}
	{/if}
{/block}

{block name='frontend_checkout_cart_item_price'}
	{if $sBasketItem.isBonusArticle}
		<div class="grid_2">
			<div class="textright">
				
			</div>
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{block name='frontend_checkout_cart_item_details'}
	{if $sBasketItem.isBonusArticle}
		<div class="bonus_image">
			<div class="image-top"></div>
			<img class="image-text" src="{link file='frontend/_resources/images/checkout_bonus_inner.png'}" alt="" />
			<div class="image-bottom"></div>
		</div>

		<div class="basket_details">
			<a class="title" href="{$sBasketItem.linkDetails}" title="{$sBasketItem.articlename|strip_tags}">
				{$sBasketItem.articlename|strip_tags|truncate:60}
			</a>
			<p class="ordernumber">
				{se namespace="frontend/checkout/cart_item" name="CartItemInfoId"}{/se} {$sBasketItem.ordernumber}
			</p>
			<p>
                {s name='CheckoutItemPrice' namespace="frontend/checkout/confirm_item"}{/s} {$sBasketItem.points_per_unit}P.
            </p>

		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
