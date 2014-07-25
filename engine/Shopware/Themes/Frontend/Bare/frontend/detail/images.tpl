{* Thumbnails *}
{if $sArticle.images}

	{* Thumbnail - Container *}
	<div class="image--thumbnails image-slider--thumbnails">

		{* Thumbnail - Slide Container *}
		<div class="image-slider--thumbnails-slide">
			{block name='frontend_detail_image_thumbnail_items'}

				{* Thumbnail - Main image *}
				{if $sArticle.image.src.5}
					<a href="{$sArticle.image.src.5}"
					   class="thumbnail--link is--active"
					   title="{if $sArticle.image.res.description}{$sArticle.image.res.description|escape:"html"}{else}{$sArticle.articleName|escape:"html"}{/if}">

						{block name='frontend_detail_image_thumbs_main_img'}
							<img class="thumbnail--image" src="{$sArticle.image.src.1}" alt="{if $sArticle.image.res.description}{$sArticle.image.res.description|escape:"html"}{else}{$sArticle.articleName|escape:"html"}{/if}">
						{/block}
					</a>
				{/if}

				{* Thumbnails *}
				{foreach $sArticle.images as $image}
					{block name='frontend_detail_image_thumbnail_images'}
						<a href="{$image.src.5}"
						   class="thumbnail--link"
						   title="{if $image.res.description}{$image.res.description|escape:"html"}{else}{$sArticle.articleName|escape:"html"}{/if}">

							{block name='frontend_detail_image_thumbs_images_img'}
								<img class="thumbnail--image" src="{$image.src.1}" alt="{if $image.res.description}{$image.res.description}{else}{$sArticle.articleName}{/if}">
							{/block}
						</a>
					{/block}
				{/foreach}
			{/block}
		</div>

		<div class="thumbnails--arrow thumbnails--trigger"></div>
	</div>
{/if}