{* Campaign right top *}
{block name='frontend_listing_right_campaign_top'}
	{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightTop}
{/block}

{* @deprecated: Old Properties filter | Moved to: frontend_listing_actions_filter *}
{block name='frontend_listing_right_filter_properties'}{/block}

{* Campaign right middle *}
{block name='frontend_listing_right_campaign_middle'}
	{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightMiddle}
{/block}

{* Topseller *}
{block name='frontend_listing_right_topseller'}{/block}

{* Campaign right bottom *}
{block name='frontend_listing_right_campaign_bottom'}
	{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightBottom}
{/block}