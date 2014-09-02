<div class="logo-main block-group" role="banner">

	{* Main shop logo *}
	{block name='frontend_index_logo'}
		<div class="logo--shop block">
			<a href="{url controller='index'}" title="{"{config name=shopName}"|escape} - {"{s name='IndexLinkDefault'}{/s}"|escape}">
				<span data-picture data-alt="{"{config name=shopName}"|escape} - {"{s name='IndexLinkDefault' namespace="frontend/index/index"}{/s}"|escape}">
					<span data-src="{link file=$theme.mobileLogo}"></span>
					<span data-src="{link file=$theme.tabletLogo}" data-media="(min-width: 48em)"></span>
                    <span data-src="{link file=$theme.tabletLandscapeLogo}" data-media="(min-width: 64em)"></span>
                    <span data-src="{link file=$theme.desktopLogo}" data-media="(min-width: 78.75em)"></span>

					<noscript>
						<img src="{link file=$theme.mobileLogo}" alt="{"{config name=shopName}"|escape} - {"{s name='IndexLinkDefault'}{/s}"|escape}">
					</noscript>
				</span>
			</a>
		</div>
	{/block}

	{* Support Info *}
	{block name='frontend_index_logo_supportinfo'}
        {if $theme.checkoutHeader && {controllerAction} !== 'cart'}
            <div class="logo--supportinfo block">
                {s name='RegisterSupportInfo' namespace='frontend/register/index'}{/s}
            </div>
        {/if}
	{/block}

	{* Trusted Shops *}
	{block name='frontend_index_logo_trusted_shops'}

	{/block}
</div>