<div id="right" class="grid_3 last">
	
	{* Campaign right top *}
	{block name='frontend_listing_right_campaign_top'}
		{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightTop}
	{/block}

	{* Properties filter *}
	{block name='frontend_listing_right_filter_properties'}
		{include file='frontend/listing/filter_properties.tpl'}
	{/block}
	
	{* Supplier filter *}
	{block name='frontend_listing_right_filter_supplier'}
		{include file='frontend/listing/filter_supplier.tpl'}
	{/block}

	{* Campaign right middle *}
	{block name='frontend_listing_right_campaign_middle'}
		{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightMiddle}
	{/block}
	
	{* Topseller *}
	{block name='frontend_listing_right_topseller'}
		{include file='frontend/plugins/index/topseller.tpl'}
	{/block}
	
	{* Campaign right bottom *}
	{block name='frontend_listing_right_campaign_bottom'}
		{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightBottom}
	{/block}
</div>