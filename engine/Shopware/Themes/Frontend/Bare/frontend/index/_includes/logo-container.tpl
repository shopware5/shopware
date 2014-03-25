<div class="logo-main block-group" role="banner">

	{* Main shop logo *}
	{block name='frontend_index_logo'}
		<div class="logo--shop block">
			<a href="{url controller='index'}" title="{config name=shopName} - {s name='IndexLinkDefault'}{/s}">
				<span data-picture data-alt="{config name=shopName} - {s name='IndexLinkDefault' namespace="frontend/index/index"}{/s}">
					<span data-src="{link file='frontend/_public/src/img/logos/logo--mobile.png'}"></span>
					<span data-src="{link file='frontend/_public/src/img/logos/logo--tablet.png'}" data-media="(min-width: 47.75em)"></span>

					<noscript>
						<img src="{link file='frontend/_public/src/img/logos/logo--mobile.png'}" alt="{config name=shopName} - {s name='IndexLinkDefault'}{/s}">
					</noscript>
				</span>
			</a>
		</div>
	{/block}

	{* Trusted Shops *}
	{block name='frontend_index_logo_trusted_shops'}
		{if {config name=TSID}}
			<div class="logo--trusted-shops block">
				<a class="trusted-shops--link" href="https://www.trustedshops.com/shop/certificate.php?shop_id={config name=TSID}" title="{s name='WidgetsTrustedLogo' namespace='frontend/plugins/trusted_shops/logo'}{/s}" target="_blank">
					<img src="{link file='frontend/_public/src/img/logos/logo--trusted-shops.png'}" alt="{s name='WidgetsTrustedLogo' namespace='frontend/plugins/trusted_shops/logo'}{/s}" />
				</a>
			</div>
		{/if}
	{/block}
</div>