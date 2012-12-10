{*
 * Copyright (c) 2011 Payment Network AG
 *
 * $Date: 2012-08-14 17:08:05 +0200 (Di, 14. Aug 2012) $
 * @version Shopware Payment Network AG Multipay 1.1.0  $Id: payment_cancel.tpl 5063 2012-08-14 15:08:05Z dehn $
 * @author Payment Network AG http://www.payment-network.com (integration@sofort.com)
 *
*}
 {extends file="frontend/checkout/finish.tpl"}
{block name="frontend_index_content"}
<div class="grid_20 finish" id="center">
	<div class="teaser">
		<h2>{s name="payment_cancel" namespace="sofort_multipay_cancel"}{/s}</h2>
		<!-- Die Bezahlung mit <b>{$sofortPaymentMethod.description}</b> wurde abgebrochen</h2> -->
		{if $message neq ""}
			{assign var="errorDisplayed" value="false"}
			{if '8010'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8010" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8010.shipping_address.country_code'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8010.shipping_address.country_code" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8010.invoice_address.country_code'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8010.invoice_address.country_code" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8010.invoice_address.firstname'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8010.invoice_address.firstname" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8010.invoice_address.lastname'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8010.invoice_address.lastname" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8010.invoice_address.street'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8010.invoice_address.street" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8010.invoice_address.zipcode'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8010.invoice_address.zipcode" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8010.invoice_address.city'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8010.invoice_address.city" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8010.shipping_address.firstname'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8010.shipping_address.firstname" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8010.shipping_address.lastname'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8010.shipping_address.lastname" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8010.shipping_address.street'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8010.shipping_address.street" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8010.shipping_address.zipcode'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8010.shipping_address.zipcode" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8010.shipping_address.city'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8010.shipping_address.city" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8013'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8013" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8015'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8015" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8019'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8019" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8020'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8020" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8023'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8023" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8024'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8024" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8029'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8029" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8033.amount'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8033" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8034'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8034" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8051'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8051" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8058'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8058" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8061'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8061" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8062'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8062" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8062.invoice_address.salutation'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8062" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '8068'|in_array:$errorCodes}
				<h3 class="error">{s name="error_8068" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if '10001'|in_array:$errorCodes}
				<h3 class="error">{s name="error_10001" namespace="sofort_multipay_errors"}{/s}</h3>
				{assign var="errorDisplayed" value="true"}
			{/if}
			{if $errorDisplayed == 'false'}
				<h3 class="error">Error: {$errorString}</h3>
			{/if}
			
		{/if}
		
		<h3>
			<a href="{$checkoutUrl}">zum Warenkorb</a>
		</h3>
	</div>
	<div class="doublespace">&nbsp;</div>
	{include file="basket.tpl"}
</div>
{/block}