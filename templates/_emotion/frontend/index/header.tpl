<head>
{* Http-Tags *}
{block name="frontend_index_header_meta_http_tags"}
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />{* Always force latest IE rendering engine & Chrome Frame *}
{/block}

{* Meta-Tags *}
{block name='frontend_index_header_meta_tags'}
	<meta name="author" content="{s name='IndexMetaAuthor'}{/s}" />
	<meta name="copyright" content="{s name='IndexMetaCopyright'}{/s}" />
	<meta name="robots" content="{block name='frontend_index_header_meta_robots'}{s name='IndexMetaRobots'}{/s}{/block}" />
	<meta name="revisit-after" content="{s name='IndexMetaRevisit'}{/s}" />
	<meta name="keywords" content="{block name='frontend_index_header_meta_keywords'}{if $sCategoryContent.metaKeywords}{$sCategoryContent.metaKeywords}{else}{s name='IndexMetaKeywordsStandard'}{/s}{/if}{/block}" />
	<meta name="description" content="{block name='frontend_index_header_meta_description'}{s name='IndexMetaDescriptionStandard'}{/s}{/block}" />
	<link rel="shortcut icon" href="{s name='IndexMetaShortcutIcon'}{link file='frontend/_resources/favicon.ico'}{/s}" type="image/x-icon" />{* Favicon *}
{/block}

{* Internet Explorer 9 specific meta tags *}
{block name='frontend_index_header_meta_tags_ie9'}
	<meta name="msapplication-navbutton-color" content="{s name='IndexMetaMsNavButtonColor'}#dd4800{/s}" />{* Navbutton color *}
	<meta name="application-name" content="{config name=shopName}" />{* Pinned name *}
	<meta name="msapplication-starturl" content="{url controller='index'}" />{* Start url to launch from the shortcut *}
	<meta name="msapplication-window" content="width=1024;height=768" />{* Size of the window to launch *}
{/block}

{* Canonical link *}
{block name='frontend_index_header_canonical'}{/block}

{* RSS and Atom feeds *}
{block name="frontend_index_header_feeds"}{/block}

{* Page title *}
<title>{block name='frontend_index_header_title'}{strip}
{if $sBreadcrumb}{foreach from=$sBreadcrumb|array_reverse item=breadcrumb}{$breadcrumb.name} | {/foreach}{/if}{config name=sShopname}
{/strip}{/block}</title>

{* Stylesheets and Javascripts *}
{block name="frontend_index_header_css_screen"}
	<link type="text/css" media="all" rel="stylesheet" href="{link file='frontend/_resources/styles/framework.css'}" />
    <link type="text/css" media="all" rel="stylesheet" href="{link file='frontend/_resources/styles/style.css'}" />
    <link type="text/css" media="all" rel="stylesheet" href="{link file='frontend/_resources/styles/colors.css'}" />
    <link type="text/css" media="all" rel="stylesheet" href="{link file='frontend/_resources/styles/plugins.css'}" />
    <link type="text/css" media="all" rel="stylesheet" href="{link file='frontend/_resources/styles/enrichments.css'}" />
	<link type="text/css" media="screen, projection" rel="stylesheet" href="{link file='frontend/_resources/styles/emotion.css'}" />
{/block}

{* Print Stylesheets *}
{block name="frontend_index_header_css_print"}
	<link type="text/css" rel="stylesheet" media="print" href="{link file='frontend/_resources/styles/print.css'}" />
{/block}

{block name="frontend_index_header_javascript_jquery_lib"}

    {* Grab Google CDN's jQuery, with a protocol relative URL *}
    <script src="{link file='frontend/_resources/javascript/jquery-1.7.2.min.js'}"></script>
{/block}

{block name="frontend_index_header_javascript"}
	<script type="text/javascript">
	//<![CDATA[
	{block name="frontend_index_header_javascript_inline"}
        {* Article compare *}
        var compareCount = '{$sComparisons|count}';
        var compareMaxCount = '{config name="MaxComparisons"}';
        {literal}
        jQuery(document).ready(function() {
            jQuery.compare.setup();
        });
        {/literal}

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
	{block name="frontend_index_header_javascript_jquery"}
        <script type="text/javascript" src="{link file='frontend/_resources/javascript/jquery.shopware.js'}"></script>
		<script type="text/javascript" src="{link file='frontend/_resources/javascript/jquery.emotion.js'}"></script>

		{* Add the partner statistics widget, if configured *}
		{if !{config name=disableShopwareStatistics} }
			{include file='widgets/index/statistic_include.tpl'}
		{/if}
	{/block}
{/block}

{block name="frontend_index_header_css_ie"}
	<!--[if lte IE 8]>
		<style type="text/css" media="screen, projection">
		{block name="frontend_index_header_css_ie_screen"}
	   	{/block}
	   	</style>
	<![endif]-->
	<!--[if lte IE 6]>
		<link type="text/css" rel="stylesheet" media="all" href="{link file='frontend/_resources/styles/ie6.css'}" />
	<![endif]-->

	<!--[if lte IE 7]>
		<link type="text/css" rel="stylesheet" media="all" href="{link file='frontend/_resources/styles/ie_emotion.css'}" />
	<![endif]-->
{/block}
</head>