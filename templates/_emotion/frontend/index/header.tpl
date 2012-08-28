{extends file='parent:frontend/index/header.tpl'}

{block name="frontend_index_header_css_screen" append}
	<link type="text/css" media="screen, projection" rel="stylesheet" href="{link file='frontend/_resources/styles/emotion.css'}" />
{/block}

{* Print Stylesheets *}
{block name="frontend_index_header_css_print"}
	<link type="text/css" rel="stylesheet" media="print" href="{link file='frontend/_resources/styles/print.css'}" />
{/block}

{block name="frontend_index_header_javascript_jquery" append}
    <script type="text/javascript" src="{link file='frontend/_resources/javascript/jquery.emotion.js'}"></script>
    {include file='widgets/index/statistic_include.tpl'}
{/block}

{block name="frontend_index_header_css_ie" append}
	<!--[if lte IE 7]>
		<link type="text/css" rel="stylesheet" media="all" href="{link file='frontend/_resources/styles/ie_emotion.css'}" />
	<![endif]-->
{/block}

{* remove CSS3Pie *}
{block name="frontend_index_header_css_ie_screen"}{/block}