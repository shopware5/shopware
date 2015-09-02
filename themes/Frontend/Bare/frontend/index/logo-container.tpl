<div class="logo-main block-group" role="banner">

	{* Main shop logo *}
	{block name='frontend_index_logo'}
		<div class="logo--shop block">
			<a class="logo--link" href="{url controller='index'}" title="{"{config name=shopName}"|escape} - {"{s name='IndexLinkDefault' namespace="frontend/index/index"}{/s}"|escape}">
                <picture>
                    <source srcset="{link file=$theme.desktopLogo}" media="(min-width: 78.75em)">
                    <source srcset="{link file=$theme.tabletLandscapeLogo}" media="(min-width: 64em)">
                    <source srcset="{link file=$theme.tabletLogo}" media="(min-width: 48em)">

                    <img srcset="{link file=$theme.mobileLogo}" alt="{"{config name=shopName}"|escape} - {"{s name='IndexLinkDefault' namespace="frontend/index/index"}{/s}"|escape}" />
                </picture>
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
	{block name='frontend_index_logo_trusted_shops'}{/block}
</div>