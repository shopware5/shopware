{block name="frontend_index_header_user_points"}
	<p class="current_points">{s namespace="frontend/bonus_system" name="HeaderYourBonusPointsFinish"}Sie besitzen<strong> {$sBonusSystem.points.user} Bonuspunkte </strong>{/s}
{/block}


{* Article price *}
{block name='frontend_checkout_cart_item_price'}<div class="grid_6">&nbsp;</div>{/block}

{* Delivery informations *}
{block name='frontend_checkout_cart_item_delivery_informations'}{/block}

{* Article amount *}
{block name='frontend_checkout_cart_item_quantity'}{/block}

{block name='frontend_checkout_cart_item_delete_article'}{/block}
{block name='frontend_checkout_cart_item_voucher_delete'}{/block}
{block name='frontend_checkout_cart_item_premium_delete'}{/block}


{* Article total sum for bonus points *}
{block name='frontend_checkout_cart_item_total_sum'}
	{if $sBasketItem.isBonusArticle}
		<div class="grid_6">
			<div class="textright">
				<strong>
					{$sBasketItem.required_points} {s namespace="frontend/bonus_system" name="Points"}Punkte{/s}
				</strong>
			</div>
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

{* display spending points for the bonus voucher *}
{block name='frontend_checkout_cart_item_voucher_tax_price'}
	{if $sBasketItem.isBonusVoucher}
		<div class="grid_10 textright bold">
			{$sBasketItem.required_points} Punkte
		</div>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
