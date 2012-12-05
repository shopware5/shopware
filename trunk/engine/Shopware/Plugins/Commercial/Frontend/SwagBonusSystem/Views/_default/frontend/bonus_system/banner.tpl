{block name="frontend_listing_banner"}
	{* Normal banner *}
	{if $sBonusSystem.settings.display_banner==1}
		{block name='frontend_listing_normal_banner'}
			<img class="bonus_banner" src="{$sBonusSystem.settings.bonus_listing_banner}" alt="" title="" />
		{/block}
	{/if}
{/block}
