{* if the user has not enough points for the basket content display the removed items *}
{if !$sBasket.content}
	{block name='frontend_basket_basket_is_empty' prepend}
		{if $sRemovedItems}
			{foreach from=$sRemovedItems item=sArticle}
				<div class="error center bold">
					{$sArticle.articleName} {s namespace="frontend/bonus_system" name="CheckoutBonusItemRemoved"}wurde aus Ihrem Warenkorb entfernt da Sie nicht ausreichend Bonuspunkte besitzen{/s}
				</div>
			{/foreach}
		{/if}
	{/block}
{else}
	{* Basket informations *}
	{block name='frontend_checkout_error_messages_basket_error' prepend}
		{if $sRemovedItems}
			{foreach from=$sRemovedItems item=sArticle}
				<div class="error center bold">
					{$sArticle.articleName} {s namespace="frontend/bonus_system" name="CheckoutBonusItemRemoved"}wurde aus Ihrem Warenkorb entfernt da Sie nicht ausreichend Bonuspunkte besitzen{/s}
				</div>
			{/foreach}
		{/if}
	{/block}
{/if}

{* display how much points the user will get for this basket content *}
{block name='frontend_checkout_cart_cart_head' prepend}
	{if $sBonusSystem.settings.bonus_system_active}
		<div class="points_for_basket">
			<div class="inner_container">
				<div class="before">{s namespace="frontend/bonus_system"  name="BasketPointsForBasketText"}Sie erhalten f&uuml;r diese Bestellung:{/s}</div>
				<div class="image">{$sBonusSystem.points.earning}</div>
				<div class="after">{s namespace="frontend/bonus_system" name="BonusPoints"}<strong><a href="{url controller='BonusSystem'}">Bonuspunkte.</a></strong>{/s}</div>
			</div>
			<div class="clear"></div>
		</div>
	{/if}
{/block}

{* if the bonus voucher is active include the slider row *}
{block name='frontend_checkout_cart_premiums'}
	{if $sBonusSystem.settings.bonus_system_active && $sBonusSystem.settings.displaySlider}
		{include file="frontend/plugins/swag_bonus_system/basket/slider_row.tpl"}
	{/if}
    {$smarty.block.parent}
{/block}

{* display the gold bonus system image for the bonus voucher *}
{block name='frontend_checkout_cart_item_voucher_details' prepend}
	{if $sBasketItem.isBonusVoucher}
		<div class="bonus_image">
			<div class="image-top"></div>
			<img class="image-text" src="{link file='frontend/_resources/images/checkout_bonus_inner.png'}" alt="" />
			<div class="image-bottom"></div>
		</div>
	{/if}
{/block}

{* display the required points in the title *}
{block name='frontend_checkout_cart_item_voucher_details'}
<div class="voucher_img">&nbsp;</div>
<div class="basket_details">
	<strong class="title">{$sBasketItem.articlename} {se name="CartTitleFor"}f&uuml;r{/se} {$sBasketItem.required_points} Punkte</strong>
	
	<p class="ordernumber">
	{se name="CartItemInfoId" namespace="frontend/checkout/cart_item"}{/se}: {$sBasketItem.ordernumber}
	</p>
</div>
{/block}

{* display the required points for the bonus voucher *}
{block name='frontend_checkout_cart_item_voucher_tax_price'}
	{if $sBasketItem.isBonusVoucher}

		<div class="grid_3 bonus_clear">
			&nbsp;
		</div>
		<div class="grid_2 bold textright">
			
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}


{* display the the item info for the bonus voucher *}
{block name='frontend_checkout_cart_item_voucher_price'}
	{if $sBasketItem.isBonusVoucher}
		<div class="grid_2">
			<div class="textright">
				<strong>
				{if $sBasketItem.itemInfo}
					{$sBasketItem.itemInfo}
				{else}
					{$sBasketItem.price|currency}{block name='frontend_checkout_cart_tax_symbol'}*{/block}
				{/if}
				</strong>
			</div>
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}


{* display the article image for bonus articles *}
{block name='frontend_checkout_cart_item_image'}
	{if $sBasketItem.isBonusArticle}
		{if $sBasketItem.image.src.0}
			<a href="{$sBasketItem.linkDetails}" title="{$sBasketItem.articleName|strip_tags}" class="thumb_image">
				<img src="{$sBasketItem.image.src.1}" border="0" alt="{$sBasketItem.articleName}" />
			</a>
		{else}
			<img class="no_image" src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{$sBasketItem.articleName}" />
		{/if}
	{else}
		{$smarty.block.parent}
	{/if}
{/block}


{* display the gold bonus item image on the left side *}
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
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{* display the shipping information for bonus articles *}
{block name='frontend_checkout_cart_item_delivery_informations'}
	{if $sBasketItem.isBonusArticle}
		<div class="grid_3">
			<div class="delivery">
                {if {config name=BasketShippingInfo}}
					{if $sBasketItem.shippinginfo}
						{include file="frontend/plugins/index/delivery_informations.tpl" sArticle=$sBasketItem}
					{else}
						&nbsp;
					{/if}
				{else}
					&nbsp;
				{/if}
			</div>
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{* the quantity box for bonus articles must modified, the max quantity will calculated by the user points *}
{block name='frontend_checkout_cart_item_quantity'}
	{if $sBasketItem.isBonusArticle}
		<div class="grid_1">
			{if (($sBonusSystem.points.remaining + $sBasketItem.required_points) / $sBasketItem.points_per_unit) >= 1}
				<select name="sQuantity" class="auto_submit">
				{section name="i" start=1 loop=($sBonusSystem.points.remaining + $sBasketItem.required_points)  / $sBasketItem.points_per_unit + 1 max=1000 step=1}
					<option value="{$smarty.section.i.index}" {if $smarty.section.i.index==$sBasketItem.quantity}selected="selected"{/if}>
							{$smarty.section.i.index}
					</option>
				{/section}
				</select>
				<input type="hidden" name="sArticle" value="{$sBasketItem.id}" />
				<input type="hidden" name="isBonusArticle" value="true" />
				<input type="hidden" name="pointsPerUnit" value="{$sBasketItem.points_per_unit}" />
			{else}
				&nbsp;
			{/if}
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{* display the required points for the bonus article *}
{block name='frontend_checkout_cart_item_price'}
	{if $sBasketItem.isBonusArticle}
		<div class="grid_2">
			<div class="textright">
				{$sBasketItem.points_per_unit}P.
			</div>
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{* display the sum of required points for the bonus article *}
{block name='frontend_checkout_cart_item_total_sum'}
	{if $sBasketItem.isBonusArticle}
		<div class="grid_2">
			<div class="textright">
				<strong>
					{$sBasketItem.required_points}P.
				</strong>
			</div>
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{* bonus articles can be deleted too *}
{block name='frontend_checkout_cart_item_delete_article'}
	{if $sBasketItem.isBonusArticle}
		<div class="action">
			<a href="{url action='deleteArticle' sDelete=$sBasketItem.id sTargetAction=$sTargetAction}" class="del" title="{s namespace="frontend/checkout/cart_item" name='CartItemLinkDelete '}{/s}">
				&nbsp;
			</a>
			&nbsp;
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{* display the sum of spending and earning points for the basket *}
{block name='frontend_checkout_cart_footer_tax_rates' append}
{if $sTargetAction == "confirm"}

{else}
	<div class="basket-points">
		{if $sUserLoggedIn}
		<div class="basket-points_left">
			<p class="spending_top">
				{se namespace="frontend/bonus_system" name="BonusSystemBasketBottomSpendingPoints"}Punkte eingel&ouml;st{/se}
			</p>
			<p class="spending_bottom">
				-{$sBonusSystem.points.spending}&nbsp;{se namespace="frontend/bonus_system" name="BonusSystemPointsMiddle"}Punkte{/se}
			</p>
		</div>
		{/if}

		<div class="basket-points_right">
		<p class="earning_top">
			{se namespace="frontend/bonus_system" name="BonusSystemBasketBottomEarningPoints"}Punkte f&uuml;r die Bestellung{/se}
		</p>
		<p class="earning_bottom">
			+{$sBonusSystem.points.earning}&nbsp;{se namespace="frontend/bonus_system" name="BonusSystemPointsMiddle"}Punkte{/se}
		</p>
		</div>
		<div class="clear">&nbsp;</div>
	</div>
{/if}
{/block}

{block name='frontend_index_content' append}
	{if $sBonusSystem.settings.bonus_articles_active && $sTargetAction=='cart' && $sBonusSystem.settings.display_article_slider==1}
		<div class="clear"></div>
		<div class="{if $sUserLoggedIn}slider-container-loggedin{else}slider-container{/if}">
			<div class="basket_article_slider">
				{include file="frontend/plugins/swag_bonus_system/recommendation/slider.tpl"}
			</div>
		</div>
	{/if}
{/block}
