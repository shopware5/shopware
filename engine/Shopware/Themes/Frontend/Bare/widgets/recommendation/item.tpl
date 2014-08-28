<div class="product-slider--item">

	<a href="{$article.linkDetails|rewrite:$article.articleName}" title="{$article.articleName|escape}" class="product--image">
		<span data-picture data-alt="{$article.articleName|escape}" class="image--element">
			<span class="image--media" data-src="{if isset($article.image.src)}{$article.image.src.4}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}"></span>
			<span class="image--media" data-src="{if isset($article.image.src)}{$article.image.src.3}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" data-media="(min-width: 48em)"></span>
			<span class="image--media" data-src="{if isset($article.image.src)}{$article.image.src.2}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" data-media="(min-width: 78.75em)"></span>

			<noscript>
				<img src="{if isset($article.image.src)}{$article.image.src.2}{else}{link file='frontend/_public/src/img/no-picture.jpg'}{/if}" alt="{$article.articleName|escape}">
			</noscript>
		</span>
	</a>

	<a title="{$article.articleName|escape}" class="product--title" href="{$article.linkDetails}">{$article.articleName|truncate:26}</a>
</div>