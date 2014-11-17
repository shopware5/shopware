{if $sCharts|@count}
<div class="topseller panel has--border is--rounded">

	<div class="topseller--title panel--title is--underline">
		{s name="TopsellerHeading" namespace=frontend/plugins/index/topseller}{/s}
	</div>

	<div class="topseller--content panel--body product-slider" data-product-slider="true">

		<div class="product-slider--container">

			{foreach $sCharts as $article}
                {include file="frontend/listing/product-box/box-product-slider.tpl"}
			{/foreach}

		</div>
	</div>
</div>
{/if}