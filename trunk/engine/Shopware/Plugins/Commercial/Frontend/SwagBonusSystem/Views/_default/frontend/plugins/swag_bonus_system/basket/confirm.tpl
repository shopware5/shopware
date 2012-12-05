{* display how much points the user will get for this basket content *}
{block name='frontend_checkout_confirm_payment' prepend}
	{if $sBonusSystem.settings.bonus_system_active}
		<div class="points_for_basket">
			<div class="inner_container">
				<div class="before">{s namespace="frontend/bonus_system"  name="BasketPointsForBasketText"}Sie erhalten f&uuml;r diese Bestellung:{/s}</div>
				<div class="image">{$sBonusSystem.points.earning}</div>
				<div class="after">{s namespace="frontend/bonus_system" name="BonusPoints"}<strong><a href="{url controller='BonusSystem'}">Bonuspunkte</a></strong>{/s}</div>
			</div>
			<div class="clear"></div>
		</div>
	{/if}
{/block}

{* if the bonus voucher is active include the slider row *}
{block name='frontend_checkout_confirm_premiums' prepend}
	{if $sBonusSystem.settings.bonus_system_active && $sBonusSystem.settings.displaySlider}
		{include file="frontend/plugins/swag_bonus_system/basket/slider_row.tpl"}
	{/if}
{/block}

{* don't display inline details for bonus articles *}
{block name='frontend_checkout_cart_item_details_inline'}
	{if !$sBasketItem.isBonusArticle}
		{$smarty.block.parent}
	{/if}
{/block}

{* don't display tax prices of the article for bonus articles *}
{block name='frontend_checkout_cart_item_tax_price'}
	{if !$sBasketItem.isBonusArticle}
		{$smarty.block.parent}
	{/if}
{/block}