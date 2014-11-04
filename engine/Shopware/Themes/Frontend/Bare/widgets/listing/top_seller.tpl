{if $sCharts|@count}
<div class="topseller panel has--border">

	<div class="topseller--title panel--title is--underline">
		{s name="TopsellerHeading" namespace=frontend/plugins/index/topseller}{/s}
	</div>

	<div class="topseller--content panel--body" data-product-slider="true">

		<div class="product-slider--container">

			{foreach $sCharts as $article}

				{assign var=image value=$article.image.src.2}

				<div class="topseller--product product-slider--item">
					<span class="topseller--number badge is--secondary">{$article@index + 1}</span>

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

			{/foreach}

		</div>
	</div>
</div>
{/if}