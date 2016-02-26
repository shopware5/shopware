
{block name="frontend_campaign_right"}
<div id="right" class="grid_3 last">
	{* Campaign right top *}
	{block name='frontend_campaign_right_top'}
		{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightTop}
	{/block}

	{* Campaign right middle *}
	{block name='frontend_campaign_right_middle'}
		{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightMiddle}
	{/block}
		
	{* Campaign right bottom *}
	{block name='frontend_campaign_right_bottom'}
		{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightBottom}
	{/block}
</div>
{/block}
