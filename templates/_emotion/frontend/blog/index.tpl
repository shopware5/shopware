{extends file='frontend/index/index.tpl'}

{block name='frontend_index_header'}
	{include file='frontend/blog/header.tpl'}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div class="grid_13" id="blog">
	{* Banner *}
	{block name='frontend_blog_index_banner'}
		{include file="frontend/listing/banner.tpl"}
	{/block}
	{* Blog listing *}
	{block name='frontend_blog_index_listing'}
		{include file="frontend/blog/listing.tpl"}
	{/block}
</div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}
<div id="right" class="grid_3 last">
	
	{* Campaign top *}
	{block name='frontend_blog_index_campaign_top'}
		{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftTop}
	{/block}
	
	<div class="blog_navi">
	
		{* Subscribe Atom + RSS *}
		{block name='frontend_blog_index_subscribe'}
		<h2 class="headingbox">{s name="BlogSubscribe"}Subscribe{/s}</h2>
		<div class="blogInteract">
			<ul>
				<li><a class="rss" href="{$sCategoryContent.rssFeed}" title="{$sCategoryContent.description}">{se name="BlogLinkRSS"}{/se}</a></li>
				<li class="last"><a class="atom" href="{$sCategoryContent.atomFeed}" title="{$sCategoryContent.description}">{se name="BlogLinkAtom"}{/se}</a></li>
			</ul>
		</div>
		{/block}

		{* Campaign Middle *}
		{block name='frontend_blog_index_campaign_middle'}
			{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftMiddle}
		{/block}
	
		{* Blog filter *}
		{block name='frontend_blog_index_filter'}
			{include file="frontend/blog/filter.tpl"}
		{/block}
	</div>
	{* Campaign bottom *}
	{block name='frontend_blog_index_campaign_bottom'}
		{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftBottom}
	{/block}
</div>
{/block}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}