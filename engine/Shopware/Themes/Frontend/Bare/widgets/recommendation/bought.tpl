{if $boughtArticles}
	<script type="text/javascript">
		(function() {
			window.widgets = (typeof(window.widgets) == 'undefined') ? [] : window.widgets;
			window.widgets.push({
				selector: '.bought--content',
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

    <div class="bought--products panel">
		<div class="bought--content panel--body product-slider" data-mode="local">
			<div class="product-slider--container">
				{foreach $boughtArticles as $article}
					{include file="widgets/recommendation/item.tpl" article=$article}
				{/foreach}
			</div>
		</div>
    </div>
{/if}