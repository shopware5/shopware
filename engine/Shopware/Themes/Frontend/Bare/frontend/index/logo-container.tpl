<div class="logo-main block-group" role="banner">

	{* Main shop logo *}
	{block name='frontend_index_logo'}
		<div class="logo--shop block">
			<a href="{url controller='index'}" title="{config name=shopName} - {s name='IndexLinkDefault'}{/s}">
				<span data-picture data-alt="{config name=shopName} - {s name='IndexLinkDefault' namespace="frontend/index/index"}{/s}">
					<span data-src="{link file=$theme.mobileLogo}"></span>
					<span data-src="{link file=$theme.tabletLogo}" data-media="(min-width: 48em)"></span>
                    <span data-src="{link file=$theme.tabletLandscapeLogo}" data-media="(min-width: 64em)"></span>
                    <span data-src="{link file=$theme.desktopLogo}" data-media="(min-width: 78.75em)"></span>

					<noscript>
						<img src="{link file=$theme.mobileLogo}" alt="{config name=shopName} - {s name='IndexLinkDefault'}{/s}">
					</noscript>
				</span>
			</a>
		</div>
	{/block}

	{* Support Info *}
	{block name='frontend_index_logo_supportinfo'}
        <div class="logo--supportinfo block">
            {s name='RegisterSupportInfo' namespace='frontend/register/index'}{/s}
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