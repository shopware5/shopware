{* display the accordion *}
{block name='frontend_index_left_trustedshops' prepend}
	{if $sBonusSystem.settings.bonus_articles_active && $sBonusSystem.settings.display_accordion==1}
		{include file='frontend/plugins/swag_bonus_system/accordion/accordion.tpl'}
	{/if}
{/block}
