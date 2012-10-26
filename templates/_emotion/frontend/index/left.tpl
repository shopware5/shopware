{extends file='parent:frontend/index/left.tpl'}

{* Campaign left top *}
{block name='frontend_index_left_campaigns_top'}
	{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftTop}
{/block}

{* Campaign left middle *}
{block name='frontend_index_left_campaigns_middle'}
	{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftMiddle}
{/block}


{* Last articles *}
{block name='frontend_index_left_last_articles'}{/block}

{* Static sites *}
{block name='frontend_index_left_menu'}{/block}
	
{block name='frontend_index_left_campaigns_bottom'}
	{include file='frontend/index/menu_left.tpl'}
    {include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftBottom}
{/block}
