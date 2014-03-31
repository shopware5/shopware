{* Thumbnails *}
{if $sArticle.images}

	{* Thumbnail - Container *}
	<div class="image--thumbnails">

		{* Thumb - Main image *}
		{block name='frontend_detail_image_thumbs_main'}
			{if $sArticle.image.src.4}
				<a href="{$sArticle.image.src.5}"
				   title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}"
				   data-small-img="{$sArticle.image.src.2}"
				   data-medium-img="{$sArticle.image.src.3}"
				   data-large-img="{$sArticle.image.src.4}"
				   data-xlarge-img="{$sArticle.image.src.3}"{block name='frontend_detail_image_thumbs_additional_queries'}{/block}>

					{block name='frontend_detail_image_thumbs_main_img'}
						<img src="{$sArticle.image.src.2}" alt="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}">
					{/block}
				</a>
			{/if}

			{* Loop through available images *}
			{foreach $sArticle.images as $image}
				{block name='frontend_detail_image_thumbs_images'}
				<a href="{$sArticle.image.src.5}"
				   title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}"
				   data-small-img="{$sArticle.image.src.2}"
				   data-medium-img="{$sArticle.image.src.3}"
				   data-large-img="{$sArticle.image.src.4}"
				   data-xlarge-img="{$sArticle.image.src.3}"{block name='frontend_detail_image_thumbs_images_additional_queries'}{/block}>

					{block name='frontend_detail_image_thumbs_images_img'}
						<img src="{$sArticle.image.src.2}" alt="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}">
					{/block}
				</a>
				{/block}
			{/foreach}
		{/block}
	</div>
{/if}