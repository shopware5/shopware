<div class="listing-blog">
	{if $sCategoryContent.cmsheadline || $sCategoryContent.cmstext}
		{include file="frontend/listing/text.tpl"}
	{/if}

	{block name="frontend_blog_listing_sidebar"}
		{include file='frontend/blog/listing_sidebar.tpl'}
	{/block}

    {if $sBlogArticles}
        {foreach from=$sBlogArticles item=sArticle key=key name="counter"}
			{include file="frontend/blog/box.tpl" sArticle=$sArticle key=$key}
		{/foreach}

		{* Paging *}
		{block name="frontend_listing_bottom_paging"}
			{include file='frontend/blog/listing_actions.tpl'}
		{/block}
	{/if}
</div>

