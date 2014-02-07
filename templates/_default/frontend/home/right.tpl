<div id="right" class="grid_3 last">
	
	{* Campaign right top *}
	{block name='frontend_home_right_campaign_top'}
		{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightTop}
	{/block}

	{* Campaign right middle *}
	{block name='frontend_home_right_campaign_middle'}
		{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightMiddle}
	{/block}
	
	{* Topseller *}
	{block name='frontend_home_right_topseller'}{/block}
	
	{* Campaign right bottom *}
	{block name='frontend_home_right_campaign_bottom'}
		{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightBottom}
	{/block}
</div>