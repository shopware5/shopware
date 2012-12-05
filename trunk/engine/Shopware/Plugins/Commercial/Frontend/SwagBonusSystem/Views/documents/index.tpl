{extends file="templates/_default/documents/index.tpl"}



{block name="document_index_table_price"}
	{if $position.isBonusArticle}
		<td align="right" width="10%" valign="top">
			{$position.points_per_unit} Punkte
		</td>
		<td align="right" width="12%" valign="top">
			{$position.required_points} Punkte
		</td>
	{elseif $position.isBonusVoucher}
		<td align="right" width="10%" valign="top">
			{$position.required_points} Punkte
		</td>
		<td align="right" width="12%" valign="top">
			{$position.amount|currency}
		</td>
	{elseif $Document.netto != true && $Document.nettoPositions != true}
		<td align="right" width="10%" valign="top">
			{$position.price|currency}
		</td>
		<td align="right" width="12%" valign="top">
			{$position.amount|currency}
		</td>
	{else}
		<td align="right" width="10%" valign="top">
			{$position.netto|currency}
		</td>
		<td align="right" width="12%" valign="top">
			{$position.amount_netto|currency}
		</td>
	{/if}
{/block}


{block name="document_index_amount" append}
	<style type="text/css">
		/*	=SUM OF THE SPENDING AND EARNING BONUS POINTS
			-------------------------------------------- */
		.basket-points {
			position: relative;
			float: right;
			background: #f5f5f5;
			margin: 10px 15px 0 0 ;
			width: 250px;
			padding: 0 20px
		}
		.basket-points p {
			float: left;
			line-height: 12px; font-size: 12px; font-weight: 700;
			padding: 5px 0;
			margin: 0 !important
		}
		.basket-points .spending_left,
		.basket-points .earning_left { width: 160px }

		.basket-points .spending_left,
		.basket-points .spending_right { border-bottom: 1px solid #e2e2e2; margin-top: 10px !important; }

		.basket-points .earning_right,
		.basket-points .spending_right { text-align: right; width: 90px }

		.basket-points .earning_right {	color: #297721 }
		.basket-points .spending_right { color: #ad0f03 }
		
		.basket-points .earning_right,
		.basket-points .earning_left { top: 1px; }
	</style>


	<div class="basket-points">
		<p class="spending_left">
			{se namespace="frontend/bonus_system" name="BonusSystemBasketBottomSpendingPoints"}Punkte eingel&ouml;st{/se}
		</p>
		<p class="spending_right">
			-{$Points.spending}{se namespace="frontend/bonus_system" name="BonusSystemPointsMiddle"} Punkte{/se}
		</p>
		<div class="clear">&nbsp;</div>

		<p class="earning_left">
			{se namespace="frontend/bonus_system" name="BonusSystemBasketBottomEarningPoints"}Punkte f&uuml;r die Bestellung{/se}
		</p>
		<p class="earning_right">
			+{$Points.earning}{se namespace="frontend/bonus_system" name="BonusSystemPointsMiddle"} Punkte{/se}
		</p>
		<div class="clear">&nbsp;</div>
	</div>
{/block}