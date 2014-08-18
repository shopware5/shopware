{block name="frontend_detail_image"}

	{* Product image - Thumbnails *}
	{block name='frontend_detail_image_thumbs'}
		{include file="frontend/detail/images.tpl"}
	{/block}

	{* Product image - Gallery *}
	{block name="frontend_detail_image_box"}
		<div class="image-slider--container">
			<div class="image-slider--slide">

				<div class="image--box image-slider--item">
					<span data-picture
						  data-alt="{if $sArticle.image.res.description}{$sArticle.image.res.description|escape:"html"}{else}{$sArticle.articleName|escape:"html"}{/if}"
						  data-img-large="{$sArticle.image.src.5}"
						  data-img-small="{$sArticle.image.src.2}"
						  data-img-original="{$sArticle.image.src.original}"
						  class="image--element">

						{*Image based on our default media queries *}
						{block name='frontend_detail_image_default_queries'}
							<span class="image--media" data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}"></span>
							<span class="image--media" data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.5}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 48em)"></span>
						{/block}

						{*Block to add additional image based on media queries *}
						{block name='frontend_detail_image_additional_queries'}{/block}

						{*If the browser doesn't support JS, the following image will be used *}
						{block name='frontend_detail_image_fallback'}
							<noscript>
								<img itemprop="image" src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" alt="{$sArticle.articleName|escape:"html"}">
							</noscript>
						{/block}
					</span>
				</div>

				{foreach $sArticle.images as $image}
					<div class="image--box image-slider--item">
						<span data-picture
							  data-alt="{if $image.res.description}{$image.res.description|escape:"html"}{else}{$sArticle.articleName|escape:"html"}{/if}"
							  data-img-large="{$image.src.5}"
							  data-img-small="{$image.src.2}"
							  data-img-original="{$image.src.original}"
							  class="image--element">

							{*Image based on our default media queries *}
							{block name='frontend_detail_image_default_queries'}
								<span class="image--media" data-src="{if isset($image.src)}{$image.src.4}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}"></span>
								<span class="image--media" data-src="{if isset($image.src)}{$image.src.5}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 48em)"></span>
							{/block}

							{*Block to add additional image based on media queries *}
							{block name='frontend_detail_image_additional_queries'}{/block}

							{*If the browser doesn't support JS, the following image will be used *}
							{block name='frontend_detail_image_fallback'}
								<noscript>
									<img itemprop="image" src="{if isset($image.src)}{$image.src.4}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" alt="{$sArticle.articleName|escape:"html"}">
								</noscript>
							{/block}
						</span>
					</div>
				{/foreach}
			</div>
		</div>
	{/block}

	{* Product image - Dot navigation *}
	{block name='frontend_detail_image_box_dots'}
		{if $sArticle.images}
			<div class="image--dots image-slider--dots panel--dot-nav">
				<a href="#" class="dot--link is--active">&nbsp;</a>
				{foreach $sArticle.images as $image}
					<a href="#" class="dot--link">&nbsp;</a>
				{/foreach}
			</div>
		{/if}
	{/block}
{/block}