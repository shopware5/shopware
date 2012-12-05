{extends file='_default/frontend/listing/index.tpl'}

{block name="frontend_listing_index_banner"}
	{if $sBonusSystem.settings.bonus_articles_active}
		{include file='frontend/bonus_system/banner.tpl'}
	{/if}
{/block}

{* Category headline *}
{block name="frontend_listing_index_text"}
	{if $sBonusSystem.settings.bonus_articles_active}
		{include file='frontend/bonus_system/text.tpl'}
	{/if}
{/block}

{* Listing *}
{block name="frontend_listing_index_listing"}
	{if $sBonusSystem.settings.bonus_articles_active}
		{include file='frontend/bonus_system/listing.tpl' sTemplate=$sTemplate}
	{/if}
{/block}

