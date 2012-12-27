{if $sTrustedShop.article}
	{* set to true if the trusted shop article isn't already in basket *}
	<div class="table_row trusted_shops">
		<form method="post" action="{url controller='checkout' action='addArticle' sAdd=$sTrustedShop.article.tsProductID sTargetAction=$sTargetAction}">
			<img class="logo_large" src="{link file='frontend/_resources/images/trusted_shops_logo_large.png'}" >
			<div class="text">
				<h5>{s name="BasketTrustedShopHeadline"}Trusted Shops Käuferschutz (empfohlen){/s}</h5>
				<p>
					{s name="BasketTrustedShopInfoText"}
						Käuferschutz bis {$sTrustedShop.article.protectedAmountDecimal|currency} ({$sTrustedShop.article.grossFee|currency} inkl. MwSt.). Die im Käuferschutz enthaltene
						<a title="Trusted Shops" href="http://www.trustedshops.com/shop/protection_conditions.php?shop_id={$sTrustedShop.id}" target="_blank">
							Trusted Shops Garantie
						</a>
						sichert Ihren Online-Kauf ab. Mit der Übermittlung und
						<a title="Trusted Shops" href="http://www.trustedshops.com/shop/data_privacy.php?shop_id={$sTrustedShop.id}" target="_blank">
							Speicherung
						</a>
						meiner E-Mail-Adresse zur Abwicklung des Käuferschutzes durch Trusted Shops bin ich einverstanden.
						<a title="Trusted Shops" href="http://www.trustedshops.com/shop/protection_conditions.php?shop_id={$sTrustedShop.id}" target="_blank">
							Garantiebedingungen
						</a>
						für den Käuferschutz
					{/s}
				</p>
				{if $sTrustedShop.displayProtectionBox}
					<div class="add_button">
						<img class="button_image" src="{link file='frontend/_resources/images/trusted_shops_logo_small.png'}" >
						<input type="submit" class="button-right small" id="trusted_shop" value="{s name='BasketAddTrustedShop'}Käuferschutz hinzufügen{/s}" />
					</div>
				{/if}
			</div>
		</form>
	</div>
{/if}