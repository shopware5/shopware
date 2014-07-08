{extends file='frontend/index/index.tpl'}

{block name='frontend_index_header'}
	{include file='frontend/blog/header.tpl'}
{/block}

{* Main content *}
{block name='frontend_index_content'}
	<div class="blog--content block-group">

		<div class="action--filter-btn">
			<a href="#"
			   class="filter--trigger btn btn--primary"
			   data-collapseTarget=".action--filter-options"
			   data-offcanvas="true"
			   data-offCanvasSelector=".action--filter-options"
			   data-closeButtonSelector=".filter--close-btn">
				<i class="icon--filter"></i> {s name='ListingFilterButton'}Filter{/s}
			</a>
		</div>

		{block name="frontend_blog_listing_sidebar"}
			{include file='frontend/blog/listing_sidebar.tpl'}
		{/block}

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