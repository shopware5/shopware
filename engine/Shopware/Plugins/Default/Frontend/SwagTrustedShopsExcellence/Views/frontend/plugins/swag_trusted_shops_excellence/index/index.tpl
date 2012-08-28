{block name='frontend_index_header_css_screen' append}
	<link type="text/css" media="all" rel="stylesheet" href="{link file='frontend/_resources/styles/trusted_shop.css'}" />
{/block}

{block name='frontend_index_left_menu' append}
	{if $sTrustedShop.rating_active==1}
		<div class="ts_rating_box">
			<a target="_blank" href="{$sTrustedShop.rating_link}" title="See customer reviews of {config name=shopName}">
				<img src="{link file='images/ts_rating.gif'}" >
			</a>
		</div>
	{/if}
{/block} 