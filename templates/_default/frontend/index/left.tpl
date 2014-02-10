<div id="left" class="grid_4 first">
	{* Campaign left top *}
	{block name='frontend_index_left_campaigns_top'}
		{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftTop}
	{/block}
	
	{* Maincategories left *}
	{block name='frontend_index_left_categories'}
		{include file='frontend/index/categories_left.tpl'}
	{/block}			
	
	{* Campaign left middle *}
	{block name='frontend_index_left_campaigns_middle'}
		{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftMiddle}
	{/block}

	{* Trusted shops logo *}
	{block name='frontend_index_left_trustedshops'}
		{if {config name=TSID}}
			{include file='frontend/plugins/trusted_shops/logo.tpl'}
		{/if}
	{/block}

	{* Static sites *}
	{block name='frontend_index_left_menu'}
		{include file='frontend/index/menu_left.tpl'}
	{/block}

	{* Campaign left bottom *}
	{block name='frontend_index_left_campaigns_bottom'}
		{include file="frontend/campaign/box.tpl" campaignsData=$sCampaigns.leftBottom}
	{/block}
	
	{* Last articles *}
	{block name='frontend_index_left_last_articles'}{/block}
	
	<div class="clear">&nbsp;</div>
</div>