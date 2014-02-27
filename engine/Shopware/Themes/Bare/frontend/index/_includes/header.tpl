<head>
{* Http-Tags *}
{block name="frontend_index_header_meta_http_tags"}
	<meta charset="utf-8">
{/block}

{* Meta-Tags *}
{block name='frontend_index_header_meta_tags'}
	<meta name="author" content="{s name='IndexMetaAuthor'}{/s}" />
	{* <meta name="copyright" content="{s name='IndexMetaCopyright'}{/s}" /> *}
	<meta name="robots" content="{block name='frontend_index_header_meta_robots'}{s name='IndexMetaRobots'}{/s}{/block}" />
	<meta name="revisit-after" content="{s name='IndexMetaRevisit'}{/s}" />
	<meta name="keywords" content="{block name='frontend_index_header_meta_keywords'}{if $sCategoryContent.metakeywords}{$sCategoryContent.metakeywords}{else}{s name='IndexMetaKeywordsStandard'}{/s}{/if}{/block}" />
	<meta name="description" content="{block name='frontend_index_header_meta_description'}{s name='IndexMetaDescriptionStandard'}{/s}{/block}" />

    <meta itemprop="copyrightHolder" content="{config name=sShopname}" />
    <meta itemprop="copyrightYear" content="{s name='IndexMetaCopyrightYear'}2014{/s}" />
    <meta itemprop="isFamilyFriendly" content="{s name='IndexMetaIsFamilyFriendly'}true{/s}" />

    {* @TODO - Replace with config option *}
    <meta itemprop="image" content="{link file='frontend/_public/src/img/logo.png'}" />

	<meta name="viewport" content="width=device-width, initial-scale=1, minimal-ui">

	{* @TODO - Add snippets or config options here *}
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
{/block}

{* Set favicons and touch icons for all different sizes *}
{block name="frontend_index_header_favicons"}
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="{link file='frontend/_resources/favicon.ico'}">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="{link file='frontend/_resources/favicon.ico'}">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="{link file='frontend/_resources/favicon.ico'}">
	<link rel="apple-touch-icon-precomposed" href="{link file='frontend/_resources/favicon.ico'}">
	<link rel="shortcut icon" sizes="196x196" href="{link file='frontend/_resources/favicon.ico'}">
	<link rel="shortcut icon" href="{link file='frontend/_resources/favicon.ico'}">
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
<title itemprop="name">{block name='frontend_index_header_title'}{strip}
{if $sBreadcrumb}{foreach from=$sBreadcrumb|array_reverse item=breadcrumb}{$breadcrumb.name} | {/foreach}{/if}{config name=sShopname}
{/strip}{/block}</title>

{* Stylesheets *}
{block name="frontend_index_header_css_screen"}
	<link href="{link file="frontend/_public/dist/all.css"}" media="screen" rel="stylesheet" type="text/css" />
{/block}

{* Print Stylesheets *}
{block name="frontend_index_header_css_print"}{/block}

{* Add Modernizr in the "<head>"-element to have all the classes before the page was rendered *}
{block name="frontend_index_header_javascript_modernizr_lib"}
	<script src="{link file='frontend/_public/vendors/modernizr/modernizr.custom.35977.js'}"></script>
{/block}

{* Block for IE specific stylesheets - @deprecated due to the stylesheets are now merged *}
{block name="frontend_index_header_css_ie"}{/block}
</head>