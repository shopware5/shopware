<div class="blog--comments block">

	{* List comments *}
	{block name='frontend_blog_comments_entry'}
		{include file='frontend/blog/comment/entry.tpl'}
	{/block}

	{* Detail Comment Form *}
	{block name='frontend_blog_comments_form'}
		{include file='frontend/blog/comment/form.tpl'}
	{/block}
</div>
