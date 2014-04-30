<script type="text/javascript">

(function() {

	window.widgets = (typeof(window.widgets) == 'undefined') ? [] : window.widgets;

	window.widgets.push({

		selector: '.topseller--content',

		plugin: 'productSlider',

		configSmartphone: {
			perPage: 1,
			perSlide: 1,
			touchControl: true
		},

		configTablet: {
			perPage: 3,
			perSlide: 1,
			touchControl: true
		},

		configTabletLandscape: {
			perPage: 4,
			perSlide: 1,
			touchControl: true
		},

		configDesktop: {
			perPage: 5,
			perSlide: 1
		}
	});

})();

</script>	

{if $sCharts|@count}
<div class="topseller panel has--border">

	<div class="topseller--title panel--title is--underline">
		{s name="TopsellerHeading" namespace=frontend/plugins/index/topseller}{/s}
	</div>

	<div class="topseller--content panel--body product-slider" data-mode="local">

		<div class="product-slider--container">

			{foreach $sCharts as $article}

				{assign var=image value=$article.image.src.2}

				<div class="topseller--product product-slider--item">
					<span class="topseller--number badge is--secondary">{$article@index + 1}</span>

					<a href="{$article.linkDetails|rewrite:$article.articleName}" title="{$article.articleName}" class="product--image">
						<span data-picture data-alt="{$article.articleName}" class="image--element">
							<span class="image--media" data-src="{if isset($article.image.src)}{$article.image.src.4}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}"></span>
							<span class="image--media" data-src="{if isset($article.image.src)}{$article.image.src.3}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 48em)"></span>
							<span class="image--media" data-src="{if isset($article.image.src)}{$article.image.src.2}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" data-media="(min-width: 78.75em)"></span>

							<noscript>
								<img src="{if isset($article.image.src)}{$article.image.src.2}{else}{link file='frontend/_resources/images/no_picture.jpg'}{/if}" alt="{$article.articleName}">
							</noscript>
						</span>
					</a>

					<a title="{$article.articleName}" class="product--title" href="{$article.linkDetails}">{$article.articleName|truncate:26}</a>
				</div>

			{/foreach}

		</div>
	</div>
</div>
{/if}