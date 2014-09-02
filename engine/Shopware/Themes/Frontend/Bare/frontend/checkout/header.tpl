<header class="header-main">

	{* Hide top bar navigation *}
	{block name='frontend_index_top_bar_container'}{/block}

	<div class="container">

		{* Logo container *}
		{block name='frontend_index_logo_container'}
			<div class="logo-main block-group" role="banner">

				{* Main shop logo *}
				{block name='frontend_index_logo'}
					<div class="logo--shop block">
						<a href="{url controller='index'}" title="{"{config name=shopName}"|escape} - {"{s name='IndexLinkDefault'}{/s}"|escape}">
							<span data-picture data-alt="{"{config name=shopName}"|escape} - {"{s name='IndexLinkDefault' namespace="frontend/index/index"}{/s}"|escape}">
								<span data-src="{link file='frontend/_public/src/img/logos/logo--mobile.png'}"></span>
								<span data-src="{link file='frontend/_public/src/img/logos/logo--tablet.png'}" data-media="(min-width: 47.75em)"></span>

								<noscript>
									<img src="{link file='frontend/_public/src/img/logos/logo--mobile.png'}" alt="{"{config name=shopName}"|escape} - {"{s name='IndexLinkDefault'}{/s}"|escape}">
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

				{/block}
			</div>
		{/block}

		{* Hide Shop navigation *}
		{block name='frontend_index_shop_navigation'}{/block}
	</div>
</header>

{* Hide Maincategories navigation top *}
{block name='frontend_index_navigation_categories_top'}{/block}