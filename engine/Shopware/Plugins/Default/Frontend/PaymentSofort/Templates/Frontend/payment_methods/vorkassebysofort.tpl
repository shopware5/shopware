{*
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-07-09 11:10:01 +0200 (Mon, 09 Jul 2012) $
 * @version Shopware SOFORT AG Multipay 1.1.0 $Id: vorkassebysofort.tpl 4656 2012-07-09 09:10:01Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
*}

<div class="grid_10 last">
	<!-- {$payment_mean.additionaldescription} -->
	{block name='vorkassebysofort'}
		<div class="debit">
			{if $sofortPaymentMeans[$payment_mean.id].name == 'vorkassebysofort_multipay' and $svCustomerProtection == 'on'}
				<!--<p><input type="checkbox" name="vorkassebysofort_cp" {$sv_customer_protection} /> {s name="admin.customerprotection_activated" namespace="sofort_multipay_backend"}{/s}</p>-->
			{/if}
			{if 'vorkassebysofort_dhw_not_accepted'|in_array:$errors}
				<!--  {s name="sofort_multipay_holder" namespace="sofort_multipay_finish"}{/s} -->
				<div class="error"><b>{s name="sofort_multipay_accept_conditions" namespace="sofort_multipay_finish"}{/s}</b></div>
			{/if}
			
			<p><input type="checkbox" name="vorkassebysofort_dhw" {$vorkassebysofort_dhw_checked} />{$dhwNoticeSV}</p>
			<div id="vorkassebysofort_dhw" style="">
				{$vorkassebysofort_dhw}
			</div>
		</div>
	{/block}
</div>
