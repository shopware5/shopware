{extends file='frontend/index/index.tpl'}

{block name="frontend_index_content_top"}{/block}

{block name='frontend_index_content_left'}
    {include file="frontend/index/_includes/sidebar.tpl"}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div class="content block">

	{block name='frontend_home_index_banner'}
		{* Banner *}
		{include file='frontend/listing/banner.tpl'}
	{/block}
	
	{block name='frontend_home_index_liveshopping'}
		{* Liveshopping *}
		{include file='frontend/listing/liveshopping.tpl'}
	{/block}
	
	{block name='frontend_home_index_text'}
		{* Category headline *}
		{if !$hasEmotion}
			{include file='frontend/listing/text.tpl'}
		{/if}
	{/block}

	{* Promotion *}
	{block name='frontend_home_index_promotions'}
		{action module=widgets controller=emotion action=index categoryId=$sCategoryContent.id controllerName=$Controller}
	{/block}
	
	{block name='frontend_home_index_blog'}
		{* Blog Articles *}
		{if $sBlog.sArticles|@count}
		<div class="listing_box">
			<h2 class="headingbox_nobg largesize">{se name='WidgetsBlogHeadline'}{/se}:</h2>
			{foreach from=$sBlog.sArticles item=article key=key name="counter"}
				{include file="frontend/blog/box.tpl" sArticle=$article key=$key homepage=true}
			{/foreach}
		</div>
		{/if} 
	{/block}

	{* Tagcloud *}
	{block name='frontend_home_index_tagcloud'}
		{if {config name=show namespace=TagCloud } && !$isEmotionLandingPage}
			{action module=widgets controller=listing action=tag_cloud}
		{/if}
	{/block}
</div>
{/block}