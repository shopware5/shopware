{block name="frontend_index_left_menu" prepend}
	{if $piKlarnaConfig.showLogos=="links"}
		<ul id="servicenav">
	        <li class="heading">
	            <span class="frontend_index_menu_left shopware_studio_snippet">{$pi_Klarna_lang['LeftSidebarHeader']}</span>
	        </li>
	    {if $pi_klarna_active}
	        <li>
	            <a href="https://{$pi_Klarna_lang['invoice']['ahref']}" title="{$pi_Klarna_lang['invoice']['href']}" target="_blank">
	                <img src="{$piKlarnaImgDir|cat:'KlarnaInvoiceLogo.png'}" class="KlarnaLeftSidebarInvoiceImg" />
	            </a>
	        </li>
	    {/if}
	    {if $pi_klarna_rate_active}
	        <li>
	            <a href="https://{$pi_Klarna_lang['rate']['ahref']}" title="{$pi_Klarna_lang['rate']['href']}" target="_blank">
	                <img src="{$piKlarnaImgDir|cat:'KlarnaRatepayLogo.png'}" class="KlarnaLeftSidebarRatepayImg" />
	            </a>
	        </li>
	    {/if}
		</ul><br/>
	{/if}
	{if ($piKlarnaConfig.RatepayBanner == "links" || $piKlarnaConfig.InvoiceBanner == "links") && $piKlarnaShopLang == "de" && $piKlarnaConfig.piKlarnaShowOneBanner}
		<a href="https://{$pi_Klarna_lang['both']['ahref']}" title="{$pi_Klarna_lang['klarna_href']}" target="_blank">
		    <img src="{$piKlarnaImgDir|cat:'KlarnaBanner.png'}" class="KlarnaBannerBothLeft" />
		</a>
	{else}
		{if $piKlarnaConfig.RatepayBanner=="links"}
		<a href="https://{$pi_Klarna_lang['both']['ahref']}" title="{$pi_Klarna_lang['rate']['href']}" target="_blank">
		    <img src="{$piKlarnaImgDir|cat:'KlarnaRatepayBanner.png'}" class="KlarnaBannerRatepayLeft" />
		</a>
		{/if}
		{if $piKlarnaConfig.InvoiceBanner=="links"}
		<a href="https://{$pi_Klarna_lang['both']['ahref']}" title="{$pi_Klarna_lang['invoice']['href']}" target="_blank">
		    <img src="{$piKlarnaImgDir|cat:'KlarnaInvoiceBanner.png'}" class="KlarnaBannerInvoiceLeft" />
		</a><br/>
		{/if}
	{/if}
{/block}