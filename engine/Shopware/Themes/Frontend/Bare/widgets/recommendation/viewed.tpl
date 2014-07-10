{if $viewedArticles}
	<script type="text/javascript">
		(function() {
			window.widgets = (typeof(window.widgets) == 'undefined') ? [] : window.widgets;
			window.widgets.push({
				selector: '.viewed--content',
				plugin: 'productSlider',
				smartphone: {
					perPage: 1,
					perSlide: 1,
					touchControl: true
				},
				tablet: {
					perPage: 3,
					perSlide: 1,
					touchControl: true
				},
				tabletLandscape: {
					perPage: 4,
					perSlide: 1,
					touchControl: true
				},
				desktop: {
					perPage: 5,
					perSlide: 1
				}
			});
		})();
	</script>

	<div class="viewed--products panel">
		<div class="viewed--content panel--body product-slider" data-mode="local">
			<div class="product-slider--container">
				{foreach $viewedArticles as $article}
					{include file="widgets/recommendation/item.tpl" article=$article}
				{/foreach}
			</div>
		</div>
	</div>
{/if}