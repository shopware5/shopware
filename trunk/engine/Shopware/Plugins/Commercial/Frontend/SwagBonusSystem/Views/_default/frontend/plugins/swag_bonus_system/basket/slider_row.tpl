{* This ist the bonus system voucher slider component, which allows the user to convert the bonus points into a voucher *}
<div class="basket_slider table_row">
	<div class="header">
		{s namespace="frontend/bonus_system" name="BasketBonusVoucherHeadline"}Jetzt Ihre Bonuspunkte als Gutschein(EUR) verrechnen:{/s}
	</div>
	<div class="inner-content">

		{* User points *}
		<div class="headline">
			<p class="points">{s namespace="frontend/bonus_system" name="BasketYourBonusPoints"}Ihr Bonuspunktestand: {$sBonusSystem.points.remaining} Bonuspunkte{/s}</p>
			<p class="conversion_max">{s namespace="frontend/bonus_system" name="BasketMaxConversion"}{$sBonusSystem.settings.bonus_voucher_conversion_factor|intval} Bonuspunkte = {1|currency} | Maximal {$sBonusSystem.settings.sliderMaxInEuro|currency} m&ouml;glich{/s}</p>
		</div>
		<div class="clear"></div>

		{* Slider Component*}
		<div class="slider"></div>
		<div class="slider-info">
			<div class="slider-info-top"></div>
			<div class="slider-info-bottom">
				{s namespace="frontend/bonus_system" name="BasketSliderInfoText"}Um den Wert bestimmen zu k&ouml;nnen, ziehen Sie den Slider in eine Richtung{/s}
			</div>
		</div>
		<div class="current_conversion">
			{s namespace="frontend/bonus_system" name="BasketSliderCurrentConversion"}1P. / {1/$sBonusSystem.settings.bonus_voucher_conversion_factor|currency}*{/s}
		</div>

		<form method="post" class="add_bonus_voucher_form" action="{url controller='BonusSystem' action='addVoucher' sTargetAction=$sTargetAction}">
			<input type="hidden" id="currency_display" value="{0|currency}*" />
			<input type="hidden" id="slider_max" value="{$sBonusSystem.settings.sliderMaxInPoints}" />
			<input type="hidden" id="conversion_factor" value="{$sBonusSystem.settings.bonus_voucher_conversion_factor}" />
			<input type="hidden" id="voucher_points" name="points" type="text" value="1" class="ordernum text" readonly />
			<input type="hidden" id="voucher_value" name="value" type="text" value="" class="ordernum text" readonly />
			<input type="submit" class="add_bonus_voucher_button button-right" value="{s namespace="frontend/bonus_system" name='BasketAddBonusVoucher'}Punkte einl&ouml;sen{/s}" />
		</form>
	</div>
</div>
<div class="clear"></div>