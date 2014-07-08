{block name="frontend_detail_image"}

	{* Product image - Thumbnails *}
	{block name='frontend_detail_image_thumbs'}
		{include file="frontend/detail/images.tpl"}
	{/block}

	{* Product image - uses the picturefill polyfill for the HTML5 "picture" element *}
	{block name='frontend_detail_image_main'}
	<span data-picture data-alt="{if $sArticle.image.res.description}{$sArticle.image.res.description}{else}{$sArticle.articleName}{/if}" class="image--element">

		{* Image based on our default media queries *}
		{block name='frontend_detail_image_default_queries'}
			<span data-src="{if isset($sArticle.image.src)}{$sArticle.image.src.5}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}"></span>
		{/block}

		{* Block to add additional image based on media queries *}
		{block name='frontend_detail_image_additional_queries'}{/block}

		{* If the browser doesn't support JS, the following image will be used *}
		{block name='frontend_detail_image_fallback'}
			<noscript>
				<img itemprop="image" src="{if isset($sArticle.image.src)}{$sArticle.image.src.4}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" alt="{$sArticle.articleName}">
			</noscript>
		{/block}
	</span>
	{/block}
{/block}