{* Article picture *}
{if $sArticle.preview.thumbNails.3}
	<div class="blog--detail-image-container block">

		{* Main Image *}
		{block name='frontend_blog_images_main_image'}
			<div class="blog--detail-images block">
				<a href="{$sArticle.preview.media.path}"
                   data-lightbox="true"
				   title="{if $sArticle.preview.media.description}{$sArticle.preview.media.description}{else}{$sArticle.title|escape}{/if}"
				   class="link--blog-image">

                    <img src="{$sArticle.preview.thumbNails.3}"
                         class="blog--image panel has--border is--rounded"
                         alt="{$sArticle.title|escape}"
                         title="{if $sArticle.preview.media.description}{$sArticle.preview.media.description}{else}{$sArticle.title|escape}{/if}" />
				</a>
			</div>
		{/block}

		{* Thumbnails *}
		{if $sArticle.media}
			{block name='frontend_blog_images_thumbnails'}
				<div class="blog--detail-thumbnails block">
					{foreach $sArticle.media as $sArticleMedia}
						{if !$sArticleMedia.preview}
							<a href="{link file=$sArticleMedia.media.path}" data-lightbox="true"
                               class="blog--thumbnail panel has--border is--rounded block"
                               data-lightbox="true"
							   title="{if $sArticleMedia.description}{$sArticleMedia.description}{else}{$sArticle.title|escape}{/if}">

                               <img class="blog--thumbnail-image" src="{link file=$sArticleMedia.thumbNails.1}" />
							</a>
						{/if}
					{/foreach}
				</div>
			{/block}
		{/if}
	</div>
{/if}