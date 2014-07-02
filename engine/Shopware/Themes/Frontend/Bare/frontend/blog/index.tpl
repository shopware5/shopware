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

{* Hide sidebar left *}
{block name='frontend_index_content_left'}{/block}