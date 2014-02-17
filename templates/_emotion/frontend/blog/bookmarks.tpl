
{if !$sArticle.sBookmarks}
	{block name='frontend_blog_bookmarks_bookmarks'}
	<div class="bookmarks">
		{* Twitter *}
		{block name='frontend_blog_bookmarks_twitter'}
		<a href="http://twitter.com/home?status={$sArticle.title|escape:'url'}+-+{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}"
			title="Twittere diesen Artikel!" 
			class="bookmark twitter" 
			rel="nofollow" 
			target="_blank">
		{se name="BookmarkTwitter"}{/se}
		</a>
		{/block}
		
		{* Facebook *}
		{block name='frontend_blog_bookmarks_facebook'}
		<a href="http://www.facebook.com/share.php?v=4&amp;src=bm&amp;u={url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}&amp;t={$sArticle.title|escape:'url'}"
			title="Empfehle diesen Artikel bei Facebook" 
			class="bookmark facebook" 
			rel="nofollow" 
			target="_blank">
			{se name="BookmarkFacebook"}{/se}
		</a>
		{/block}
		
		{* Del.icio.us *}
		{block name='frontend_blog_bookmarks_deliciosus'}
		<a href="http://del.icio.us/post?url={url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}&amp;title={$sArticle.title|escape:'url'}"
			title="Empfehle diesen Artikel bei del.icio.us" 
			class="bookmark delicious" 
			rel="nofollow" 
			target="_blank">
			{se name="BookmarkDelicious"}{/se}
		</a>
		{/block}
		
		{* Digg *}
		{block name='frontend_blog_bookmarks_digg'}
		<a href="http://digg.com/submit?phase=2&amp;url={url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}&amp;title={$sArticle.title|escape:'url'}"
			title="Digg this!" 
			class="bookmark digg" 
			rel="nofollow" 
			target="_blank">
			{se name="BookmarkDiggit"}{/se}
		</a>
		{/block}
	
		<hr class="clear" />
	</div>
	{/block}
{/if}
