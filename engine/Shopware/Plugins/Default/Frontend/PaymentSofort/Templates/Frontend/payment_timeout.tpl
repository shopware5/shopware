{*
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-07-09 11:10:01 +0200 (Mon, 09 Jul 2012) $
 * @version Shopware SOFORT AG Multipay 1.1.0  $Id: payment_timeout.tpl 4656 2012-07-09 09:10:01Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
*}
 {extends file="frontend/checkout/finish.tpl"}
{block name="frontend_index_content"}
<div class="grid_20 finish" id="center">
	<div class="teaser">
		<h2>Timeout der Bezahlung mit <b>{$sofortPaymentMethod.description}</b> </h2>
	<h3>
		<a href="{$checkoutUrl}">zum Warenkorb</a>
	</h3>
	</div>
	<div class="doublespace">&nbsp;</div>
	{include file="basket.tpl"}
</div>
{/block}