{extends file='frontend/listing/listing.tpl'}

{block name="frontend_listing_top_actions"}
{/block}

{block name="frontend_listing_list_inline"}
	{if $sBonusSystem.settings.bonus_articles_active}
		{* Actual listing *}
		{foreach $sBonusSystem.articles as $sArticle}
			<div class="bonus-article">
				{include file="frontend/bonus_system/box_article.tpl" sTemplate=$sTemplate lastitem=$sArticle@last firstitem=$sArticle@first}
			</div>
		{/foreach}
	{/if}
{/block}

{* Paging *}
{block name="frontend_listing_bottom_paging"}
{/block}
