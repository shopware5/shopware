{*
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-09-05 12:43:34 +0200 (Mi, 05 Sep 2012) $
 * @version Shopware SOFORT AG Multipay 1.1.0 $Id: sofortlastschrift.tpl 5277 2012-09-05 10:43:34Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
*}

<div class="grid_10 last">
	{if $slBannerOrText eq 1}
		{$payment_mean.additionaldescription}
	{else}
		<div id="sofortlastschrift_logo"><a href="{s name="sofort_multipay_sl_landing_url" namespace="sofort_multipay_bootstrap"}{/s}" target="_blank" ><img src="{s name="sofort_multipay_sl_banner_img2" namespace="sofort_multipay_bootstrap"}{/s}" alt="{s name="sofort_multipay_sl_banner_img_alt" namespace="sofort_multipay_bootstrap"}{/s}" /></a></div>
		{s name="checkout.sl.description" namespace="sofort_multipay_checkout"}{/s}
	{/if}
	{block name='sofortlastschrift'}
		<div class="debit">
		<!--
			<p class="none">
			<label for="kontonr">{s name="sofort_multipay_account_number" namespace="sofort_multipay_finish"}{/s}*:</label>
			<input type="text" class="text " id="kontonr" name="sofortlastschrift_account_number" value="{$bankAccount['accountNumber']}">
			</p>
			<p class="none">
			<label for="blz">{s name="sofort_multipay_bank_code" namespace="sofort_multipay_checkout"}{/s}*:</label>
			<input type="text" class="text " id="blz" name="sofortlastschrift_bank_code" value="{$bankAccount['bankCode']}">
			</p>
			<p class="none">
			<label for="bank2">{s name="sofort_multipay_holder" namespace="sofort_multipay_checkout"}{/s}*:</label>
			<input type="text" class="text " id="bank2" name="sofortlastschrift_holder" value="{$bankAccount['holder']}">
			</p>
			<p class="description">{s name="mandatory_fields" namespace="sofort_multipay_checkout"}{/s}
			</p>
			-->
		</div>
	{/block}
</div>