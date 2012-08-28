{extends file='parent:frontend/index/left.tpl'}

{* Last articles *}
{block name='frontend_index_left_last_articles'}{/block}

{* Static sites *}
{block name='frontend_index_left_menu'}{/block}
	
{block name='frontend_index_left_campaigns_bottom' append}
	{include file='frontend/index/menu_left.tpl'}
{/block}
