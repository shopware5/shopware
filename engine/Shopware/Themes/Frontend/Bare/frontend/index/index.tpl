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
				{* Include the top bar navigation *}
				{include file="frontend/index/_includes/topbar-navigation.tpl"}

				<div class="container">

					{* Logo container *}
					{block name='frontend_index_logo_container'}
						{include file="frontend/index/_includes/logo-container.tpl"}
					{/block}

					{* Shop navigation *}
					{block name='frontend_index_shop_navigation'}
						{include file="frontend/index/_includes/shop-navigation.tpl"}
					{/block}
				</div>
			</header>

			{* Maincategories navigation top *}
			{block name='frontend_index_navigation_categories_top'}
				<nav class="navigation-main">
					{include file='frontend/index/_includes/main-navigation.tpl'}
				</nav>
			{/block}
		{/block}

		<section class="content-main container block-group">

			{* Breadcrumb *}
			{block name='frontend_index_breadcrumb'}
				<nav class="content--breadcrumb block">
					{include file='frontend/index/_includes/breadcrumb.tpl'}
				</nav>
			{/block}

			{* Content top container *}
			{block name="frontend_index_content_top"}{/block}

			{* Sidebar left *}
			{block name='frontend_index_content_left'}
				{include file='frontend/index/_includes/sidebar.tpl'}
			{/block}

			{* Main content *}
			{block name='frontend_index_content'}{/block}

			{* Sidebar right *}
			{block name='frontend_index_content_right'}{/block}

			{* Last seen products *}
			{block name='frontend_index_left_last_articles'}
				{if $sLastArticlesShow && !$isEmotionLandingPage}
					{include file="frontend/plugins/index/viewlast.tpl"}
				{/if}
			{/block}
		</section>

		{* Footer *}
		{block name="frontend_index_footer"}
			<footer class="footer-main">
				<div class="container">
					{block name="frontend_index_footer_container"}
						{include file='frontend/index/_includes/footer.tpl'}
					{/block}
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
