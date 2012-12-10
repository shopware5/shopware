{*
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-07-09 11:10:01 +0200 (Mon, 09 Jul 2012) $
 * @version Shopware SOFORT AG Multipay 1.1.0 $Id: sofortrechnung.tpl 4656 2012-07-09 09:10:01Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
*}

<div class="grid_10 last">
	<!-- {$payment_mean.additionaldescription} -->
	{block name='sofortrechnung'}
		<div class="sofortrechnung">
			{if 'sofortrechnung_dhw_not_accepted'|in_array:$errors}
				<div class="error"><b>{s name="sofort_multipay_accept_conditions" namespace="sofort_multipay_finish"}{/s}</b></div>
			{/if}
			<p><input type="checkbox" name="sofortrechnung_dhw" {$sofortrechnung_dhw_checked}/>{$dhwNoticeSR}</p>
			<div id="sofortrechnung_dhw" style="">
				{$sofortrechnung_dhw}
			</div>
		</div>
	{/block}
</div>