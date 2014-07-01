{extends file='frontend/index/index.tpl'}

{block name='frontend_index_header'}
	{include file='frontend/blog/header.tpl'}
{/block}

{* Main content *}
{block name='frontend_index_content'}
	<div class="blog--content">

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
	<div class="blog--sidebar">

		{* Campaign top *}
		{block name='frontend_blog_index_campaign_top'}
			{include file="frontend/campaign/box.tpl" campaignsData=$campaigns.leftTop}
		{/block}

		<div class="blog--navigation">

			{* Subscribe Atom + RSS *}
			{block name='frontend_blog_index_subscribe'}
				<div class="blog--subscribe panel has--border is--rounded">
					<h1 class="blog--subscribe-headline panel--title is--underline">{s name="BlogSubscribe"}Subscribe{/s}</h1>

					<div class="blog--subscribe-content panel--body is--wide">
						<ul>
							<li><a class="rss" href="{$sCategoryContent.rssFeed}" title="{$sCategoryContent.description}">{s name="BlogLinkRSS"}{/s}</a></li>
							<li class="last"><a class="atom" href="{$sCategoryContent.atomFeed}" title="{$sCategoryContent.description}">{s name="BlogLinkAtom"}{/s}</a></li>
						</ul>
					</div>
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