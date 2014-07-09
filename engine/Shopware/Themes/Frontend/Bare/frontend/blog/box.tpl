<div class="blog--box panel has--border is--rounded block">
	{block name='frontend_blog_col_blog_entry'}

		{* Blog Header *}
		{block name='frontend_blog-col_box_header'}
			<div class="blog--box-header">

				{* Article name *}
				{block name='frontend_blog_col_article_name'}
					<h1 class="blog--box-headline panel--title">
						<a href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}" title="{$sArticle.title}">{$sArticle.title}</a>
					</h1>
				{/block}

				{* Meta data *}
				{block name='frontend_blog_col_meta_data'}
					<div class="blog--box-metadata">

						{* Author *}
						{if $sArticle.author.name}
							<span class="blog--metadata-author is--first">{s name="BlogInfoFrom"}{/s} {$sArticle.author.name}</span>
						{/if}

						{* Date *}
						{if $sArticle.displayDate}
							<span class="blog--metadata-date{if !$sArticle.author.name} is--first{/if}">{$sArticle.displayDate|date:"DATETIME_SHORT"}</span>
						{/if}

						{* Description *}
						{if $sArticle.categoryInfo.description}
							<span class="blog--metadata-description">
								{if $sArticle.categoryInfo.linkCategory}
									<a href="{$sArticle.categoryInfo.linkCategory}" title="{$sArticle.categoryInfo.description}">{$sArticle.categoryInfo.description}</a>
								{else}
									{$sArticle.categoryInfo.description}
								{/if}
							</span>
						{/if}
							<span class="blog--metadata-description{if $sArticle.sVoteAverage|round ==0} is--last{/if}">
								<a href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}#commentcontainer" title="{$sArticle.articleName}">{if $sArticle.numberOfComments}{$sArticle.numberOfComments}{else}0{/if} {s name="BlogInfoComments"}{/s}</a>
							</span>

						{* Rating *}
						{if $sArticle.sVoteAverage|round !=0}
							<span class="blog--metadata-rating is--last star star{$sArticle.sVoteAverage|round}">{s name="BlogInfoRating"}{/s}</span>
						{/if}
					</div>
				{/block}

			</div>
		{/block}

		{* Blog Box *}
		{block name='frontend_blog_col_box_content'}
			<div class="blog--box-content panel--body is--wide block">

				{* Blog Article pictures *}
				{block name='frontend_blog_col_article_picture'}
					{if $sArticle.preview.thumbNails.2}
						<div class="blog--box-picture">
							<a href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}" class="blog--picture-main" title="{$sArticle.title}"><img class="blog--picture-preview" src="{link file=$sArticle.preview.thumbNails.2}" /></a>
						</div>
					{/if}
				{/block}

				{* Article Description *}
				{block name='frontend_blog_col_description'}
					<div class="blog--box-description">
						<p>{if $sArticle.shortDescription}{$sArticle.shortDescription|nl2br}{else}{$sArticle.shortDescription}{/if}</p>

						{* Read more button *}
						{block name='frontend_blog_col_read_more'}
							<div class="blog--box-readmore">
								<a href="{url controller=blog action=detail sCategory=$sArticle.categoryId blogArticle=$sArticle.id}" title="{$sArticle.title}" class="btn btn--primary is--small">{s name="BlogLinkMore"}{/s}</a>
							</div>
						{/block}

						{* Read more button *}
						{block name='frontend_blog_col_tags'}
							<div class="blog--box-tags">
								{if $sArticle.tags|@count > 1}
									<strong>{s name="BlogInfoTags"}Tags:{/s}</strong>
									{foreach $sArticle.tags as $tag}
										<a href="{$tag.link}" title="{$tag.name}">{$tag.name}</a>{if !$tag@last}, {/if}
									{/foreach}
								{/if}
							</div>
						{/block}

					</div>
				{/block}

			</div>
		{/block}

	{/block}
</div>