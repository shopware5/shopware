{* Thumbnails *}
{if $sArticle.images}

	{* Thumbnail - Container *}
	<div class="image--thumbnails" data-thumbnails="true">

		{* Thumb - Main image *}
		{block name='frontend_detail_image_thumbs_main'}
			{if $sArticle.image.src.4}
				<a href="{$sArticle.image.src.5}" data-slider-index="1"
				   class="is--active"
				   title="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}"
				   data-xlarge-img="{$sArticle.image.src.5}"{block name='frontend_detail_image_thumbs_additional_queries'}{/block}>

					{block name='frontend_detail_image_thumbs_main_img'}
						<img class="thumbnail--image" src="{$sArticle.image.src.1}" alt="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}">
					{/block}
				</a>
			{/if}

			{* Loop through available images *}
			{foreach $sArticle.images as $image}
				{block name='frontend_detail_image_thumbs_images'}
				<a href="{$image.src.5}" data-slider-index="{{$image@index + 2}}"
				   title="{if $image.res.description}{$image.res.description}{else}{$sArticle.articleName}{/if}"
				   data-xlarge-img="{$image.src.5}"{block name='frontend_detail_image_thumbs_images_additional_queries'}{/block}>

					{block name='frontend_detail_image_thumbs_images_img'}
						<img class="thumbnail--image" src="{$image.src.1}" alt="{if $image.res.description}{$image.res.description}{else}{$sArticle.articleName}{/if}">
					{/block}
				</a>
				{/block}
			{/foreach}
		{/block}

		<div class="thumbnails--arrow thumbnails--trigger">
			<i class="icon--arrow-right"></i>
		</div>
	</div>
{/if}