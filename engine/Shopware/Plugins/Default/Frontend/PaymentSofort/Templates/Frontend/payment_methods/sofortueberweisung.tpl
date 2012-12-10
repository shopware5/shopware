{*
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-07-09 11:10:01 +0200 (Mon, 09 Jul 2012) $
 * @version Shopware SOFORT AG Multipay 1.1.0 $Id: sofortueberweisung.tpl 4656 2012-07-09 09:10:01Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
*}

<div class="grid_10 last">
{$payment_mean.additionaldescription = ''}
	{if $suBannerOrText eq 1}
		{if $suCustomerProtection == 'on'}
			<div id="sofortueberweisung_logo"><a href="{s name='sofort_multipay_su_landing_url' namespace='sofort_multipay_bootstrap'}{/s}" target="_blank"><img src="{s name='sofort_multipay_su_banner_ks_img' namespace='sofort_multipay_bootstrap'}{/s}" alt="{s name='sofort_multipay_su_banner_img_alt' namespace='sofort_multipay_bootstrap'}{/s}" /></a></div>
		{else}
			{s name='sofort_multipay_su_banner_img' namespace='sofort_multipay_bootstrap'}{/s}
		{/if}
	{else}
		{if $suCustomerProtection == 'on'}
			<div id="sofortueberweisung_logo"><a href="{s name='sofort_multipay_su_landing_url' namespace='sofort_multipay_bootstrap'}{/s}" target="_blank"><img src="{s name='sofort_multipay_su_banner_ks_img' namespace='sofort_multipay_bootstrap'}{/s}" alt="{s name='sofort_multipay_su_banner_img_alt' namespace='sofort_multipay_bootstrap'}{/s}" /></a></div>
		{else}
			{s name='sofort_multipay_su_banner_img' namespace='sofort_multipay_bootstrap'}{/s}
		{/if}
			{s name='checkout.su.description' namespace='sofort_multipay_checkout'}{/s}
	{/if}
</div>