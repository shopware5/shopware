{extends file='parent:frontend/index/header.tpl'}

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

{block name="frontend_index_header_javascript_jquery"}
    <script type="text/javascript" src="{link file='frontend/_resources/javascript/jquery.shopware.js'}"></script>
    <script type="text/javascript" src="{link file='frontend/_resources/javascript/jquery.emotion.js'}"></script>
    {if !{config name=disableShopwareStatistics} }
        {include file='widgets/index/statistic_include.tpl'}
    {/if}
{/block}

{block name="frontend_index_header_css_ie"}
    <!--[if lte IE 8]>
        <style type="text/css" media="screen, projection">
        {block name="frontend_index_header_css_ie_screen"}
        .viewlast .article_image, #detail #detailinfo .similar .artbox .artbox_thumb,.table_premium div.body div.article, div.table_foot input.button_tablefoot, .button-left, .button-middle, .button-right, #trustedShopsLogo .inner_container, #paypalLogo .inner_container, #paypalLogo_noborder .inner_container, #basketButton,.small_green, #basket .actions a,#content #buybox .basketform .accessory_overlay,#registerbutton {ldelim}
            behavior: url("{link file='frontend/_resources/PIE.htc'}");
        {rdelim}
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

{* remove CSS3Pie *}
{block name="frontend_index_header_css_ie_screen"}{/block}