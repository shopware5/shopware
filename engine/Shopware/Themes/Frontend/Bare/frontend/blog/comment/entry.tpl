{namespace name="frontend/blog/comments"}

{if $sArticle.comments}
<div class="blog--comments-list panel--body is--wide">

	{* Comments headline *}
	{block name='frontend_blog_comments_comment_headline'}
		<h1 class="blog--comments-headline" id="commentcontainer">{$sArticle.comments|count} {s name="BlogInfoComments"}{/s}</h1>
	{/block}

	{* List comments *}
	{block name='frontend_blog_comments_comment'}
		<ul class="list--unstyled">
			{foreach $sArticle.comments as $vote}
				{block name='frontend_blog_comments_comment_block'}
					<li class="blog--comments-entry">
						<div class="blog--comments-entry-inner panel has--border is--rounded{if $vote@last} is--last{/if}">

							{* Comment Header *}
							{block name='frontend_blog_comments_comment_left'}
								<div class="blog--comments-entry-left panel--body is--wide">

									{* Stars *}
									{block name='frontend_blog_comments_comment_rating'}
										<div class="blog--comments-rating">
                                            {include file="frontend/_includes/rating.tpl" points=$vote.points}
										</div>
									{/block}

									{* Author *}
									{block name='frontend_blog_comments_comment_author'}
										<div class="blog--comments-author" itemscope itemtype="http://schema.org/UserComments">

											{block name='frontned_blog_comments_comment_author_label'}
												<strong class="content--label">{s name="DetailCommentInfoFrom" namespace='frontend/detail/comment'}{/s}</strong>
											{/block}

											{block name='frontned_blog_comments_comment_author_name'}
												<span class="comments--author" itemprop="creator">{$vote.name}</span>
											{/block}
										</div>
									{/block}

									{* Date *}
									{block name='frontend_blog_comments_comment_date'}
										<div class="blog--comments-date">

											{block name='frontend_blog_commetns_comment_date_label'}
												<strong class="content--label">{s name="DetailCommentInfoAt" namespace='frontend/detail/comment'}Am:{/s}</strong>
											{/block}

											{block name='frontend_blog_comments_comment_date_creationdate'}
												<span class="comments--date" itemprop="commentTime">{$vote.creationDate|date:date_long}</span>
											{/block}
										</div>
									{/block}
								</div>
							{/block}

							{* Comment Content *}
							{block name='frontend_blog_comments_comment_right'}
								<div class="blog--comments-entry-right panel--body is--wide">

									{* Headline *}
									{block name='frontend_blog_comments_comment_headline'}
										<h2 class="blog--comments-entry-headline">{$vote.headline}</h2>
									{/block}

									{* Comment *}
									{block name='frontend_blog_comments_comment_text'}
										<div class="blog--comments-entry-text">{$vote.comment|nl2br}</div>
									{/block}
								</div>
							{/block}
						</div>
					</li>
				{/block}
			{/foreach}
		</ul>
	{/block}

</div>
{/if}