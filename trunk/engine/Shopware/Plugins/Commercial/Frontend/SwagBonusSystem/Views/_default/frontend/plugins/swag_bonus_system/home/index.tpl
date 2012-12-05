{* Promotion *}
{block name='frontend_home_index_promotions' prepend}
	{if $sBonusSystem.settings.bonus_articles_active && $sBonusSystem.settings.display_article_slider==1}
		{include file="frontend/plugins/swag_bonus_system/recommendation/slider.tpl"}
	{/if}
{/block}
