{if $Data}
	<div class="category-teaser--box panel--box">

		{* Category teaser image *}
		<a class="box--image" href="{if $Data.blog_category}{url controller=blog action=index sCategory=$Data.category_selection}{else}{url controller=cat action=index sCategory=$Data.category_selection}{/if}" title="{$Data.categoryName|strip_tags}">
			<span data-picture data-alt="{config name=shopName} - {s name='IndexLinkDefault' namespace="frontend/index/index"}{/s}" class="image--element">
				<span data-src="{if isset($Data.images)}{$Data.images.2}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}"></span>
				<span data-src="{if isset($Data.images)}{$Data.images.3}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 47.75em)"></span>
				<span data-src="{if isset($Data.images)}{$Data.images.4}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 64em)"></span>
				<span data-src="{if isset($Data.images)}{$Data.images.5}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 120em)"></span>

				<noscript>
					<img src="{if isset($Data.images)}{$Data.image.3}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" alt="{$Data.categoryName|strip_tags}">
				</noscript>
			</span>

			{* Category teaser title *}
			<a class="box--title" href="{if $Data.blog_category}{url controller=blog action=index sCategory=$Data.category_selection}{else}{url controller=cat action=index sCategory=$Data.category_selection}{/if}" title="{$Data.categoryName|strip_tags}">
				{$Data.categoryName}
			</a>
		</a>
	</div>
{/if}