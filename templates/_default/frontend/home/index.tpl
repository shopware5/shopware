{extends file='frontend/index/index.tpl'}

{block name="frontend_index_content_top"}
	
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13 home">
	
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
		{include file='frontend/listing/text.tpl'}
	{/block}
	
	{block name='frontend_home_index_promotions'}
		{* Promotion *}
		{include file='frontend/listing/promotions.tpl'}
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
	
	
	<div class="doublespace">&nbsp;</div>
	
	{block name='frontend_home_index_tagcloud'}
		{* Tagcloud *}
		{include file='frontend/plugins/index/tagcloud.tpl'}
	{/block}
</div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}
	{include file='frontend/home/right.tpl'}
{/block}