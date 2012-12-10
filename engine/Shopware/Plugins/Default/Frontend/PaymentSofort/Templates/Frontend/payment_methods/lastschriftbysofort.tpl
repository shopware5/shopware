{*
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-09-05 13:02:30 +0200 (Mi, 05 Sep 2012) $
 * @version Shopware SOFORT AG Multipay 1.1.0 $Id: lastschriftbysofort.tpl 5282 2012-09-05 11:02:30Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
*}

<div class="grid_10 last">
	<!--  {$payment_mean.additionaldescription}-->
	{block name='lastschriftbysofort'}
		<div class="debit">
			<p class="none">
			{if 'lastschriftbysofort_account_number'|in_array:$errors}
				<div class="error"><b>{s name="error_missing_bankdata" namespace="sofort_multipay_bootstrap"}{/s}</b></div>
			{/if}
			<label for="kontonr">{s name="sofort_multipay_account_number" namespace="sofort_multipay_finish"}{/s}*:</label>
			<input type="text" class="text " id="kontonr" name="lastschriftbysofort_account_number" value="{$bankAccount['ls_account_number']}">
			</p>
			<p class="none">
			{if 'lastschriftbysofort_bank_code'|in_array:$errors}
				<div class="error"><b>{s name="error_missing_bankdata" namespace="sofort_multipay_bootstrap"}{/s}</b></div>
			{/if}
			<label for="blz">{s name="sofort_multipay_bank_code" namespace="sofort_multipay_finish"}{/s}*:</label>
			<input type="text" class="text " id="blz" name="lastschriftbysofort_bank_code" value="{$bankAccount['ls_bank_code']}">
			</p>
			<p class="none">
			{if 'lastschriftbysofort_holder'|in_array:$errors}
				<div class="error"><b>{s name="error_missing_bankdata" namespace="sofort_multipay_bootstrap"}{/s}</b></div>
			{/if}
			<label for="bank2">{s name="sofort_multipay_holder" namespace="sofort_multipay_finish"}{/s}*:</label>
			<input type="text" class="text " id="bank2" name="lastschriftbysofort_holder" value="{$bankAccount['ls_holder']}">
			</p>
			<p class="description">{s name="mandatory_fields" namespace="sofort_multipay_checkout"}{/s}
			</p>
			<p>
			{if 'lastschriftbysofort_dhw_not_accepted'|in_array:$errors}
				<div class="error"><b>{s name="sofort_multipay_accept_conditions" namespace="sofort_multipay_finish"}{/s}</b></div>
			{/if}
			<input type="checkbox" name="lastschriftbysofort_dhw" {$lastschriftbysofort_dhw_checked}/>{$dhwNoticeLS}</p>
			<div id="lastschriftbysofort_dhw" style="">
				{$lastschriftbysofort_dhw}
			</div>
		</div>
	{/block}
</div>