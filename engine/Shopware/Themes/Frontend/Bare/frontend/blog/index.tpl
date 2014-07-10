{extends file='frontend/index/index.tpl'}

{block name='frontend_index_header'}
	{include file='frontend/blog/header.tpl'}
{/block}

{* Main content *}
{block name='frontend_index_content'}
	<div class="blog--content block-group">

		{* Blog Filter Button *}
		{block name='frontend_blog_listing_filter_button'}
			<div class="blog--filter-btn">
				<a href="#"
				   class="filter--trigger btn btn--primary"
				   data-collapseTarget=".blog--filter-options"
				   data-offcanvas="true"
				   data-offCanvasSelector=".blog--filter-options"
				   data-closeButtonSelector=".blog--filter-close-btn">
					<i class="icon--filter"></i> {s name='ListingFilterButton'}Filter{/s}
				</a>
			</div>
		{/block}

		{* Blog Sidebar *}
		{block name='frontend_blog_listing_sidebar'}
			{include file='frontend/blog/listing_sidebar.tpl'}
		{/block}

		{* Blog Banner *}
		{block name='frontend_blog_index_banner'}
			{include file="frontend/listing/banner.tpl"}
		{/block}

		{* Blog listing *}
		{block name='frontend_blog_index_listing'}
			{include file="frontend/blog/listing.tpl"}
		{/block}

		{* Blog Pagination *}
		{block name='frontend_blog_index_pagination'}
			<div class="blog--paging block">
				{include file='frontend/listing/actions/action-pagination.tpl'}
			</div>
		{/block}
	</div>
{/block}