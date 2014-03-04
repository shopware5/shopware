{block name="frontend_index_start"}{/block}
{block name="frontend_index_doctype"}
<!DOCTYPE html>
{/block}

{block name='frontend_index_html'}
<html class="no-js" lang="{s name='IndexXmlLang'}de{/s}" itemscope="itemscope" itemtype="http://schema.org/WebPage">
{/block}

{block name='frontend_index_header'}
	{include file='frontend/index/_includes/header.tpl'}
{/block}

<body class="{if $Controller}is--ctl-{$Controller}{/if}">
	<div class="page-wrap">

		{* Message if javascript is disabled *}
		{block name="frontend_index_no_script_message"}
			<noscript class="noscript-main">
				{s name="IndexNoscriptNotice"}{/s}
			</noscript>
		{/block}

		{block name='frontend_index_before_page'}{/block}

		{* Shop header *}
		{block name='frontend_index_navigation'}
			<header class="header-main">
				<div class="container">
					<div class="top-bar block-group">

						{* Language and Currency switcher *}
						{block name='frontend_index_actions'}
							<div class="top-bar--switches block">
								{action module=widgets controller=index action=shopMenu}
							</div>
						{/block}

						{* Top bar navigation *}
						{block name="frontend_index_top_bar_nav"}
							<nav class="top-bar--navigation block">
								<ul class="navigation--list" role="menubar">

									{* Compare - TODO - Check syntax *}
									{block name='frontend_index_navigation_inline'}
										{if $sCompareShow}
											<li class="navigation--entry entry--compare" role="menuitem" aria-haspopup="true">
												{action module=widgets controller=compare}
											</li>
										{/if}
									{/block}

									{* Notepad *}
									{block name="frontend_index_checkout_actions_notepad"}
										<li class="navigation--entry entry--notepad" role="menuitem">
											<a href="{url controller='note'}" title="{s namespace='frontend/index/checkout_actions' name='IndexLinkNotepad'}{/s}" class="note">
												{s namespace='frontend/index/checkout_actions' name='IndexLinkNotepad'}{/s} {if $sNotesQuantity > 0}<span class="notes_quantity">{$sNotesQuantity}</span>{/if}
											</a>
										</li>
									{/block}

									{* Service / Support drop down *}
									{block name="frontend_index_checkout_actions_service_menu"}
										<li class="navigation--entry entry--service has--drop-down" role="menuitem" aria-haspopup="true">
											{s name='IndexLinkService'}Service/Hilfe{/s}
											{action module=widgets controller=index action=menu group=gLeft}
										</li>
									{/block}
								</ul>
							</nav>
						{/block}
					</div>

					{* Logo container *}
					{block name='frontend_index_logo_container'}
						<div class="logo-main block-group" role="banner">

							{* Main shop logo *}
							{block name='frontend_index_logo'}
								<div class="logo--shop block">
									<a href="{url controller='index'}" title="{config name=shopName} - {s name='IndexLinkDefault'}{/s}">

										<img src="{link file='frontend/_public/src/img/logos/logo--mobile.png'}" alt="{config name=shopName} - {s name='IndexLinkDefault'}{/s}" />
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
					{/block}

					{* Shop navigation *}
					<nav class="shop--navigation block-group">
						<ul class="navigation--list block-group" role="menubar">

							{* Menu (Off canvas left) trigger *}
							{block name='frontend_index_offcanvas_left_trigger'}
								<li class="navigation--entry entry--menu-left block" role="menuitem">
									<a class="entry--link entry--trigger" href="#offcanvas--left">
										<i class="icon--menu"></i> Menü
									</a>
								</li>
							{/block}

							{* Spacer *}
							{block name="frontend_index_shop_navigation_spacer"}
								<li class="navigation--entry entry--spacer block"></li>
							{/block}

							{* Search form *}
							{block name='frontend_index_search'}
								<li class="navigation--entry entry--search block" role="menuitem">
									<a class="entry--link entry--trigger" href="#show-hide--search" title="Suche anzeigen / ausblenden">
										<i class="icon--search"></i>
									</a>
									{include file="frontend/index/_includes/search.tpl"}
								</li>
							{/block}

							{* My account entry *}
							{block name="frontend_index_checkout_actions_my_options"}
								<li class="navigation--entry entry--account block" role="menuitem">
									{block name="frontend_index_checkout_actions_account"}
										<a href="{url controller='account'}" title="{s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}" class="entry--link">
											{s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}
										</a>
									{/block}
								</li>
							{/block}

							{* Cart entry *}
							{block name='frontend_index_checkout_actions'}
								<li class="navigation--entry entry--cart block" role="menuitem">
									<a class="entry--link entry--trigger" href="#show-hide--search" title="Warenkorb anzeigen / ausblenden">
										<i class="icon--basket"></i>
									</a>
									{action module=widgets controller=checkout action=info}
								</li>
							{/block}
						</ul>
					</nav>
				</div>
			</header>

			{* Maincategories navigation top *}
			{block name='frontend_index_navigation_categories_top'}
				<nav class="navigation-main">
					{include file='frontend/index/_includes/categories_top.tpl'}
				</nav>
			{/block}
		{/block}

		<section class="content-main container">

			{* Breadcrumb *}
			{block name='frontend_index_breadcrumb'}
				<nav class="content--breadcrumb">
					{include file='frontend/index/_includes/breadcrumb.tpl'}
				</nav>
			{/block}

			{* Content top container *}
			{block name="frontend_index_content_top"}{/block}

			{* Sidebar left *}
			{block name='frontend_index_content_left'}
				{include file='frontend/index/_includes/left.tpl'}
			{/block}

			{* Main content *}
			{block name='frontend_index_content'}{/block}

			{* Sidebar right *}
			{block name='frontend_index_content_right'}{/block}

			{* TODO - Needs correct block *}
			{if $sLastArticlesShow && !$isEmotionLandingPage}
				{include file="frontend/plugins/index/viewlast.tpl"}
			{/if}
		</section>

		{* Footer *}
		{block name="frontend_index_footer"}
			<footer class="footer-main">
				<div class="container">
					{include file='frontend/index/_includes/footer.tpl'}
				</div>
			</footer>
		{/block}


		{block name='frontend_index_body_inline'}{/block}
	</div>

{* Include jQuery and all other javascript files at the bottom of the page *}
{block name="frontend_index_header_javascript_jquery_lib"}
	<script src="{link file='frontend/_public/dist/all.js'}"></script>
{/block}

{block name="frontend_index_header_javascript"}
    {* <script type="text/javascript">
        //<![CDATA[
        {block name="frontend_index_header_javascript_inline"}
            var timeNow = {time() nocache};

            jQuery.controller =  {ldelim}
                'vat_check_enabled': '{config name='vatcheckendabled'}',
                'vat_check_required': '{config name='vatcheckrequired'}',
                'ajax_cart': '{url controller="checkout"}',
                'ajax_search': '{url controller="ajax_search"}',
                'ajax_login': '{url controller="account" action="ajax_login"}',
                'register': '{url controller="register"}',
                'checkout': '{url controller="checkout"}',
                'ajax_logout': '{url controller="account" action="ajax_logout"}',
                'ajax_validate': '{url controller="register"}'
            {rdelim};
        {/block}
        //]]>
	</script>
	*}

	{block name="frontend_index_header_javascript_jquery"}
		{* Add the partner statistics widget, if configured *}
		{* if !{config name=disableShopwareStatistics} }
			{include file='widgets/index/statistic_include.tpl'}
		{/if *}
	{/block}

{/block}
</body>
</html>
