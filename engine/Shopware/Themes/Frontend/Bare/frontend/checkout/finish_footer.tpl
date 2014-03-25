{extends file='frontend/checkout/confirm_footer.tpl'}

{block name="frontend_checkout_cart_footer_left"}
	<div class="grid_8">&nbsp;</div>
{/block}

{block name='frontend_checkout_cart_footer_tax_information'}{/block}

{block name='frontend_checkout_cart_footer_tax_rates'}
	{if $sUserData.additional.charge_vat}
		{foreach $sBasket.sTaxRates as $rate=>$value}
			<div>
				<p class="textright">
					<strong>{$value|currency}</strong>
				</p>
			</div>
		{/foreach}
	{/if}

	{if {config name=countrynotice} && $sCountry.notice && {include file="string:{$sCountry.notice}"} !== ""}
		<div class="clear"></div>

		<div class="emotion-country_notice">
			{* Include country specific notice message *}
			<p>{include file="string:{$sCountry.notice}"}</p>
        </div>
    {/if}
{/block}