{* include the jquery UI css  *}
{block name="frontend_index_header_css_screen" append}
	<link type="text/css" media="all" rel="stylesheet" href="{link file='frontend/_resources/styles/jquery-ui-1.8.16.custom.css'}" />
{/block}

{* include the basket slider jquery and jquery UI javascript *}
{block name='frontend_index_header_javascript' append}
	<script type="text/javascript" src="{link file='frontend/_resources/javascript/jquery-ui-1.8.16.custom.min.js'}"></script>
	<script type="text/javascript" src="{link file='frontend/_resources/javascript/jquery.basket-slider.js'}"></script>
{/block}

