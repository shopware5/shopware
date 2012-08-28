{block name="frontend_index_logo" append}
	{if ($piKlarnaConfig.RatepayBanner == "oben" || $piKlarnaConfig.InvoiceBanner == "oben") && $piKlarnaShopLang == "de" && $piKlarnaConfig.piKlarnaShowOneBanner}
		<a href="https://{$pi_Klarna_lang['both']['ahref']}" title="{$pi_Klarna_lang['klarna_href']}" target="_blank">
		    <img src="{$piKlarnaImgDir|cat:'KlarnaBanner.png'}" class="KlarnaBannerBothTop" />
		</a>
	{else}
		{if $piKlarnaConfig.RatepayBanner == "oben"}
		<a href="https://{$pi_Klarna_lang['both']['ahref']}" title="{$pi_Klarna_lang['rate']['href']}" target="_blank">
		    <img src="{$piKlarnaImgDir|cat:'KlarnaRatepayBanner.png'}" class="KlarnaBannerRatepayTop" />
		</a>
		{/if}
		{if $piKlarnaConfig.InvoiceBanner =="oben"}
		<a href="https://{$pi_Klarna_lang['both']['ahref']}" title="{$pi_Klarna_lang['invoice']['href']}" target="_blank">
		    <img src="{$piKlarnaImgDir|cat:'KlarnaInvoiceBanner.png'}" class="KlarnaBannerInvoiceTop" />
		</a><br/>
		{/if}
	{/if}
{/block}
