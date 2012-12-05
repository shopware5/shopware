{block name='frontend_index_content_right' prepend}
	{if $piKlarnaConfig.showLogos == "rechts"}
	    <div class="KlarnaRightSidebar">
	    	<h2 class="headingbox" id="KlarnaRightSidebarHeader">{$pi_Klarna_lang['LeftSidebarHeader']}</h2>
			<div class="supplier">
			    <ul>
				{if $pi_klarna_active}
					<li class="image">
			            <a href="https://{$pi_Klarna_lang['invoice']['ahref']}" title="{$pi_Klarna_lang['invoice']['href']}" target="_blank">
			                <img src="{$piKlarnaImgDir|cat:'KlarnaInvoiceLogo.png'}" class="KlarnaRightSidebarInvoiceImg" />
			            </a>
			         </li>
				{/if}
				{if $pi_klarna_rate_active}
					<li class="image">
			            <a href="https://{$pi_Klarna_lang['rate']['ahref']}" title="{$pi_Klarna_lang['rate']['href']}" target="_blank">
			                <img src="{$piKlarnaImgDir|cat:'KlarnaRatepayLogo.png'}" class="KlarnaRightSidebarRatepayImg"/>
			            </a>
			        </li>
				{/if}
				</ul>
			</div>
		</div>
	{/if}
	{if ($piKlarnaConfig.RatepayBanner == "rechts" || $piKlarnaConfig.InvoiceBanner == "rechts") && $piKlarnaShopLang == "de" && $piKlarnaConfig.piKlarnaShowOneBanner}
		<a href="https://{$pi_Klarna_lang['both']['ahref']}" title="{$pi_Klarna_lang['klarna_href']}" target="_blank">
		    <img src="{$piKlarnaImgDir|cat:'KlarnaBanner.png'}" class="KlarnaBannerBothRight" />
		</a>
	{else}
		{if $piKlarnaConfig.RatepayBanner == "rechts"}
		<a href="https://{$pi_Klarna_lang['both']['ahref']}" title="{$pi_Klarna_lang['rate']['href']}" target="_blank">
		    <img src="{$piKlarnaImgDir|cat:'KlarnaRatepayBanner.png'}" class="KlarnaBannerRatepayRight" />
		</a>
		{/if}
		{if $piKlarnaConfig.InvoiceBanner =="rechts"}
		<a href="https://{$pi_Klarna_lang['both']['ahref']}" title="{$pi_Klarna_lang['invoice']['href']}" target="_blank">
		    <img src="{$piKlarnaImgDir|cat:'KlarnaInvoiceBanner.png'}" class="KlarnaBannerInvoiceRight" />
		</a><br/>
		{/if}
	{/if}
{/block}